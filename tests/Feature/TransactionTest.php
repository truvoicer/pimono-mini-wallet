<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Events\TransactionReceived;
use App\Events\TransactionSent;
use App\Models\Transaction;
use App\Services\Transaction\TransactionService;
use Illuminate\Support\Facades\Event;
use Inertia\Middleware;
use Inertia\Testing\AssertableInertia as Assert;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['balance' => 1000]);

        Event::fake();
    }

    /**
     * Helper to get the current Inertia asset version.
     */
    protected function getInertiaVersion(): string
    {
        // Get the version from the middleware implementation
        return app(Middleware::class)->version(request());
    }


    public function test_guests_cannot_access_the_transaction_index_page(): void
    {
        $this->get(route('transaction.index'))
            ->assertRedirect(route('login'));
    }


    public function test_authenticated_user_can_view_the_transaction_index(): void
    {
        $receiver = User::factory()->create();
        // Arrange: Create a transaction for the user and one for another user
        $transactions = Transaction::factory()->count(2)->create([
            'sender_id' => $this->user->id,
            'receiver_id' => $receiver->id,
        ]);
        Transaction::factory()->create([
            'sender_id' => $receiver->id,
            'receiver_id' => $this->user->id,
        ]); // Other user's transaction

        $this->actingAs($this->user)
            ->get(route('transaction.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('transaction/Index')
                    // Assert that the user data is passed to the view
                    ->has(
                        'user',
                        fn(Assert $user) => $user
                            ->where('data.id', $this->user->id)
                            ->etc()
                    )
            );
    }


    public function test_partial_reload_to_index_correctly_includes_optional_transactions_prop(): void
    {
        $receiver = User::factory()->create();
        // Arrange: Create a transaction for the user and one for another user
        $transactions = Transaction::factory()->count(2)->create([
            'sender_id' => $this->user->id,
            'receiver_id' => $receiver->id,
        ]);
        Transaction::factory()->create([
            'sender_id' => $receiver->id,
            'receiver_id' => $this->user->id,
        ]); // Other user's transaction

        $this->actingAs($this->user)
            ->withHeaders([
                'X-Inertia' => 'true', // 1. Flag the request as an Inertia visit
                'X-Inertia-Partial-Data' => 'transactions', // 2. Ask for the optional 'transactions' prop
                'X-Inertia-Partial-Component' => 'transaction/Index',
                'X-Inertia-Version' => $this->getInertiaVersion()
            ])
            ->get(route('transaction.index'))
            ->assertOk()
            ->assertJsonStructure([
                'props' => [
                    'transactions' => [
                        'data',
                        'links',
                        'meta'
                    ]
                ]
            ]);
    }

    // ========================================================================
    // CREATE METHOD TESTS
    // ========================================================================


    public function test_guests_cannot_access_the_transaction_create_page(): void
    {
        $this->get(route('transaction.create'))
            ->assertRedirect(route('login'));
    }


    public function authenticated_user_can_view_the_transaction_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('transaction.create'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('transaction/Create')
            );
    }

    // ========================================================================
    // STORE METHOD TESTS
    // ========================================================================


    public function test_guests_cannot_create_a_transaction(): void
    {
        $this->post(route('transaction.store'), [])
            ->assertRedirect(route('login'));

        // Ensure the service was never called
        $this->assertFalse(app()->bound(TransactionService::class));
    }


    public function test_transaction_store_request_requires_valid_data(): void
    {
        $this->actingAs($this->user)
            ->post(route('transaction.store'), [])
            ->assertSessionHasErrors(['receiver_id', 'amount']) // Assuming these are required fields
            ->assertRedirect();
    }


    public function test_authenticated_user_can_successfully_store_a_transaction(): void
    {
        // Arrange
        $receiver = User::factory()->create();
        $transactionData = [
            'receiver_id' => $receiver->id,
            'amount' => 500,
        ];

        // Act & Assert
        $this->actingAs($this->user)
            ->post(route('transaction.store'), $transactionData)
            ->assertRedirect(route('home')) // Assumes back() redirects to index after success
            ->assertSessionHas('success', 'Transaction successful');

        // Assert the event was dispatched (since the service mock returned successfully)
        Event::assertDispatched(TransactionSent::class);
        Event::assertDispatched(TransactionReceived::class);
    }


    public function test_transaction_store_handles_insufficient_balance_exception(): void
    {
        // Arrange
        $receiver = User::factory()->create();
        $transactionData = [
            'receiver_id' => $receiver->id,
            'amount' => 5000, // Amount that will cause failure
        ];

        // Mock the TransactionService to throw the expected business logic exception
        $mockService = $this->createMock(TransactionService::class);

        // Expect the service method to be called once and throw the exception
        $mockService->expects($this->once())
            ->method('storeTransaction')
            ->willThrowException(new \App\Exceptions\TransactionException('Insufficient balance.'));

        $this->app->instance(TransactionService::class, $mockService);

        // Act & Assert
        $this->actingAs($this->user)
            ->post(route('transaction.store'), $transactionData)
            ->assertSessionHasErrors(['error' => 'Insufficient balance.']) // Check for the exception message in the session
            ->assertRedirect(); // Should redirect back to the form

        // If the service fails, no event should be dispatched
        Event::assertNotDispatched(TransactionSent::class);
        Event::assertNotDispatched(TransactionReceived::class);
    }
}

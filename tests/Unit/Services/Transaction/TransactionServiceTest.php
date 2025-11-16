<?php

namespace Tests\Unit;

use App\Events\TransactionReceived;
use App\Events\TransactionSent;
use App\Exceptions\TransactionException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\Transaction\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;
    private MockInterface $transactionRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Mock the TransactionRepository dependency
        $this->transactionRepositoryMock = $this->mock(TransactionRepository::class);

        // 2. Instantiate the service with the mock
        $this->transactionService = new TransactionService($this->transactionRepositoryMock);

        // 3. Fake events to prevent actual dispatching during tests
        Event::fake();
    }

    public function it_throws_exception_if_receiver_id_is_missing(): void
    {
        $sender = User::factory()->create();
        $data = ['amount' => 100];

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Receiver ID is required.');

        $this->transactionService->storeTransaction($sender, $data);
    }

    public function it_throws_exception_if_receiver_user_is_not_found(): void
    {
        $sender = User::factory()->create();
        $data = ['receiver_id' => 9999, 'amount' => 100]; // ID 9999 does not exist

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Receiver user not found.');

        $this->transactionService->storeTransaction($sender, $data);
    }


    public function it_throws_exception_for_self_transaction(): void
    {
        $user = User::factory()->create();
        $data = ['receiver_id' => $user->id, 'amount' => 100];

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('You cannot send a transaction to yourself.');

        $this->transactionService->storeTransaction($user, $data);
    }


    public function it_throws_exception_for_insufficient_balance_and_rollbacks_transaction(): void
    {
        // Arrange
        $initialBalance = 100.00;
        $amount = 100.00;
        $commissionRate = 1.5; // 1.5%
        $commissionFee = $amount * ($commissionRate / 100); // 1.50
        $totalAmount = $amount + $commissionFee; // 101.50

        // Sender only has 100.00, which is < 101.50 total cost
        $sender = User::factory()->create(['balance' => $initialBalance]);
        $receiver = User::factory()->create(['balance' => 0.00]);
        $data = ['receiver_id' => $receiver->id, 'amount' => $amount];

        // The repository should never be called if the transaction rolls back
        $this->transactionRepositoryMock->shouldNotReceive('getDataBuilder');

        // Assert Exception
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Insufficient balance.');

        try {

            $this->transactionService->storeTransaction($sender, $data);
        } finally {
            // Assert Rollback: Balances must remain unchanged
            $sender->refresh();
            $receiver->refresh();

            $this->assertEquals($initialBalance, $sender->balance);
            $this->assertEquals(0.00, $receiver->balance);

            // Assert no events were dispatched
            Event::assertNotDispatched(TransactionSent::class);
            Event::assertNotDispatched(TransactionReceived::class);
        }
    }

    public function it_stores_transaction_updates_balances_and_dispatches_events_on_success(): void
    {
        $initialSenderBalance = 1000.00;
        $initialReceiverBalance = 50.00;
        $amount = 500.00;
        $commissionRate = 1.5;
        $commissionFee = $amount * ($commissionRate / 100);
        $totalAmount = $amount + $commissionFee;

        $expectedSenderBalance = $initialSenderBalance - $totalAmount;
        $expectedReceiverBalance = $initialReceiverBalance + $amount;

        $sender = User::factory()->create(['balance' => $initialSenderBalance]);
        $receiver = User::factory()->create(['balance' => $initialReceiverBalance]);
        $data = ['receiver_id' => $receiver->id, 'amount' => $amount];

        // Mock the repository chain
        Transaction::factory()->make([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'commission_fee' => $commissionFee,
        ]);

        $this->transactionRepositoryMock->shouldReceive('getDataBuilder->setData->build')
             ->once()
             ->andReturn([
                 'receiver_id' => $receiver->id,
                 'amount' => $amount,
                 'commission_fee' => $commissionFee,
                 'sender_id' => $sender->id,
             ]);

        // Mock the sentTransactions()->create() call
        $this->transactionRepositoryMock->shouldReceive('getDataBuilder')
             ->andReturnSelf();
        $this->transactionRepositoryMock->shouldReceive('setData')
             ->andReturnSelf();
        $this->transactionRepositoryMock->shouldReceive('build')
             ->andReturn([
                 'receiver_id' => $receiver->id,
                 'amount' => $amount,
                 'commission_fee' => $commissionFee,
                 'sender_id' => $sender->id,
             ]);


        $transaction = $this->transactionService->storeTransaction($sender, $data);

        //Assert transaction model instance is returned
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($sender->id, $transaction->sender_id);
        $this->assertEquals($receiver->id, $transaction->receiver_id);
        $this->assertEquals($amount, $transaction->amount);
        $this->assertEquals($commissionFee, $transaction->commission_fee);

        //Assert balances are correctly updated
        $sender->refresh();
        $receiver->refresh();

        $this->assertEquals($expectedSenderBalance, $sender->balance);
        $this->assertEquals($expectedReceiverBalance, $receiver->balance);

        // Assert 3: Events are dispatched
        Event::assertDispatched(TransactionSent::class, function ($event) use ($transaction) {
            return $event->transaction->is($transaction);
        });

        Event::assertDispatched(TransactionReceived::class, function ($event) use ($transaction) {
            return $event->transaction->is($transaction);
        });
    }
}

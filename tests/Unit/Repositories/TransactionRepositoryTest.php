<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// --- Dummy Classes for Testing ---

/**
 * Dummy Transaction model used to test repository integration.
 */
class DummyTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    public $timestamps = false;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
    ];

    protected static function newFactory()
    {
        // Simple factory to create test records
        return new class extends \Illuminate\Database\Eloquent\Factories\Factory {
            protected $model = DummyTransactionModel::class;
            public function definition()
            {
                return [
                    'sender_id' => User::factory(),
                    'receiver_id' => User::factory(),
                    'amount' => $this->faker->randomFloat(2, 10, 500),
                    'commission_fee' => $this->faker->randomFloat(2, 0.1, 5.0),
                ];
            }
        };
    }
}

class TransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $reflection = new \ReflectionClass(TransactionRepository::class);
        $modelClassProperty = $reflection->getProperty('modelClass');
        $modelClassProperty->setAccessible(true);
        $modelClassProperty->setValue(null, DummyTransactionModel::class);

        //Instantiate the repository
        $this->repository = new TransactionRepository();
    }

    public function test_it_initializes_with_a_transaction_model_query(): void
    {
        $query = $this->repository->getQuery();

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertEquals('transactions', $query->getModel()->getTable());
    }


    public function test_it_can_insert_a_transaction_record(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $data = [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => 150.00,
            'commission_fee' => 2.25,
        ];

        $transaction = $this->repository->insert($data);

        $this->assertInstanceOf(DummyTransactionModel::class, $transaction);
        $this->assertDatabaseHas('transactions', ['amount' => 150.00]);
    }


    public function test_it_can_paginate_transactions(): void
    {
        DummyTransactionModel::factory()->count(15)->create();

        $paginator = $this->repository->paginate();
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(15, $paginator->total());
        $this->assertEquals(10, $paginator->perPage());
    }
}

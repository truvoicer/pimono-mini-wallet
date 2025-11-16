<?php

namespace Tests\Unit\Repositories;

use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// --- Dummy Classes for Testing ---

/**
 * Dummy User model used to test repository integration.
 * In a real application, this would be the actual \App\Models\User.
 */
class DummyUserModel extends Model
{
    use HasFactory;

    protected $table = 'users';
    public $timestamps = false;
    protected $fillable = ['name', 'balance', 'email', 'password'];

    protected static function newFactory()
    {
        // Simple factory to create test user records
        return new class extends \Illuminate\Database\Eloquent\Factories\Factory {
            protected $model = DummyUserModel::class;
            public function definition()
            {
                return [
                    'name' => $this->faker->name,
                    'email' => $this->faker->unique()->safeEmail,
                    'password' => 'password',
                    'balance' => $this->faker->randomFloat(2, 0, 1000),
                ];
            }
        };
    }
}

// --- Actual Test Class ---

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Set up the necessary database schema
        // $this->createUserSchema();

        // 2. Temporarily bind the dummy model to the repository's model class
        // This simulates the UserRepository using \App\Models\User
        $reflection = new \ReflectionClass(UserRepository::class);
        $modelClassProperty = $reflection->getProperty('modelClass');
        $modelClassProperty->setAccessible(true);
        $modelClassProperty->setValue(null, DummyUserModel::class);

        // 3. Instantiate the repository
        $this->repository = new UserRepository();
    }

    // ========================================================================
    // MODEL ASSOCIATION & INHERITANCE TESTS
    // ========================================================================


    public function test_it_initializes_with_a_user_model_query(): void
    {
        // The query builder should point to the DummyUserModel's table
        $query = $this->repository->getQuery();

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertEquals('users', $query->getModel()->getTable());
    }


    public function test_it_can_insert_a_user_record(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secret',
            'balance' => 500.00,
        ];


        $user = $this->repository->insert($data);


        $this->assertInstanceOf(DummyUserModel::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com', 'balance' => 500.00]);
    }


    public function test_it_can_update_a_user_record(): void
    {
        // Arrange
        $user = DummyUserModel::factory()->create(['balance' => 100.00]);
        $updateData = ['balance' => 999.99];


        $result = $this->repository->update($user, $updateData);


        $this->assertTrue($result);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'balance' => 999.99]);
    }


    public function test_it_can_delete_a_user_record(): void
    {
        // Arrange
        $user = DummyUserModel::factory()->create();


        $result = $this->repository->delete($user);


        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }



    public function test_it_can_paginate_user_records(): void
    {
        // Create test data
        DummyUserModel::factory()->count(15)->create();

        $paginator = $this->repository->paginate();
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(15, $paginator->total());
        $this->assertEquals(10, $paginator->perPage());
    }
}

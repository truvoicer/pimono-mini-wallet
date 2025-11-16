<?php

namespace Tests\Unit\Repositories;

use App\Repositories\BaseRepository;
use App\Repositories\Builders\DataBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// --- Dummy Classes for Testing ---
class TestFactory extends Factory
{

    protected $model = TestModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'value' => $this->faker->numberBetween(1, 100),
        ];
    }

}

/**
 * Dummy model representing a database table for testing purposes.
 */
class TestModel extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return TestFactory::new();
    }

    public $timestamps = false;
    protected $fillable = ['name', 'value'];
    protected $table = 'test_models';
}

class TestRepository extends BaseRepository
{
    public int $perPage = 10;
}


class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        //Set up a dummy database table required by the TestModel
        $this->createSchema();

        //Instantiate the repository with a dummy model instance
        $this->repository = new TestRepository(new TestModel());
    }

    /**
     * Creates the necessary database schema for TestModel.
     */
    protected function createSchema(): void
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('value');
        });
    }


    public function it_initializes_with_an_eloquent_query_and_data_builder(): void
    {
        // Assert the initial query is an Eloquent Builder instance
        $this->assertInstanceOf(Builder::class, $this->repository->getQuery());

        // Assert the DataBuilder property is initialized
        $this->assertInstanceOf(DataBuilder::class, $this->repository->getDataBuilder());
    }


    public function it_can_set_and_get_the_query_builder(): void
    {
        $newQuery = TestModel::where('value', '>', 10);

        $this->repository->setQuery($newQuery);

        // Assert the retrieved query is the one we set
        $this->assertEquals($newQuery, $this->repository->getQuery());
    }


    public function initialize_query_resets_the_query_to_a_new_instance(): void
    {
        $originalQuery = $this->repository->getQuery();

        // Manipulate the query
        $this->repository->getQuery()->where('name', 'test');

        // Re-initialize
        $this->repository->initializeQuery();
        $newQuery = $this->repository->getQuery();

        // Assert the new query is a fresh instance (not equal to the manipulated one)
        $this->assertNotEquals($originalQuery, $newQuery);
        $this->assertInstanceOf(Builder::class, $newQuery);
    }


    public function it_can_insert_a_record_and_return_the_model(): void
    {
        $data = ['name' => 'New Item', 'value' => 50];

        $model = $this->repository->insert($data);

        // Assert the return type
        $this->assertInstanceOf(TestModel::class, $model);
        // Assert the data exists in the database
        $this->assertDatabaseHas('test_models', $data);
    }


    public function it_can_update_an_existing_record(): void
    {

        $model = TestModel::factory()->create(['name' => 'Old Name', 'value' => 10]);
        $updateData = ['name' => 'Updated Name', 'value' => 20];


        $result = $this->repository->update($model, $updateData);

        // Assert update success
        $this->assertTrue($result);
        // Assert old data is gone, and new data is present
        $this->assertDatabaseMissing('test_models', ['name' => 'Old Name']);
        $this->assertDatabaseHas('test_models', ['id' => $model->id, 'name' => 'Updated Name']);
    }


    public function it_can_delete_an_existing_record(): void
    {

        $model = TestModel::factory()->create(['name' => 'To Be Deleted', 'value' => 100]);

        $result = $this->repository->delete($model);

        // Assert delete success
        $this->assertTrue($result);
        // Assert the record is gone from the database
        $this->assertDatabaseMissing('test_models', ['id' => $model->id]);
    }


    public function it_can_paginate_the_current_query(): void
    {
        TestModel::factory()->count(20)->create();

        $paginator = $this->repository->paginate();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(20, $paginator->total());
        $this->assertEquals(10, $paginator->perPage());
    }
}

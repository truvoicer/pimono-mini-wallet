<?php

namespace Tests\Unit\Repositories\Builders;

use App\Repositories\Builders\DataBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Tests\TestCase;

// --- Dummy Classes for Testing ---

/**
 * Dummy model with a defined $fillable array for testing DataBuilder's filtering.
 */
class DataBuilderTestModel extends Model
{
    protected $fillable = [
        'name',
        'email',
        'age',
        'role_id',
    ];
}

/**
 * Dummy FormRequest implementation to simulate validated data logic.
 */
class TestFormRequest extends FormRequest
{
    public function validated($key = null, $default = null)
    {
        $data = [
            'name' => 'Validated Name',
            'email' => 'validated@test.com',
            'password' => 'secret_password',
            'extra_validated' => 'should_be_ignored',
        ];

        return $data;
    }
}

class DataBuilderTest extends TestCase
{
    private DataBuilder $builder;
    private DataBuilderTestModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DataBuilderTestModel();
        $this->builder = new DataBuilder($this->model);
    }

    public function test_it_filters_input_data_to_only_include_fillable_fields(): void
    {

        $data = [
            'name' => 'Pimono',
            'age' => 30,
            'extra_field' => 'should not be included', // Not fillable
            '_token' => 'csrf_token', // Not fillable
        ];


        $result = $this->builder->setData($data)->build();

        $this->assertEquals(['name' => 'Pimono', 'age' => 30], $result);
        $this->assertArrayNotHasKey('extra_field', $result);
    }

    public function test_it_processes_data_from_a_standard_request(): void
    {
        $request = new Request([
            'name' => 'Pimono',
            'email' => 'pimono@test.com',
            'unfillable_key' => 'ignore me',
        ]);


        $result = $this->builder->setRequest($request)->build();

        $this->assertEquals(['name' => 'Pimono', 'email' => 'pimono@test.com'], $result);
        $this->assertArrayNotHasKey('unfillable_key', $result);
    }

    public function test_it_processes_data_from_a_form_request_using_validated_data(): void
    {

        // TestFormRequest returns validated data, including 'password' which is not fillable
        $formRequest = new TestFormRequest();

        $result = $this->builder->setRequest($formRequest)->build();

        // Assert: Only 'name' and 'email' should remain (since they are fillable)
        $this->assertEquals([
            'name' => 'Validated Name',
            'email' => 'validated@test.com'
        ], $result);
        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('extra_validated', $result);
    }

    public function test_it_ignores_specified_fields_from_the_request_before_filtering(): void
    {
        $request = new Request([
            'name' => 'pimono',
            'email' => 'pimono@test.com',
            'age' => 45,
            'role_id' => 2,
        ]);

        $result = $this->builder
            ->setRequest($request)
            ->setIgnore(['role_id', 'email'])
            ->build();

        // Assert: 'role_id' and 'email' are removed, even though they are fillable
        $this->assertEquals([
            'name' => 'pimono',
            'age' => 45
        ], $result);
    }

    public function test_custom_data_overrides_request_data(): void
    {

        $request = new Request([
            'name' => 'Request Name',
            'email' => 'request@test.com',
        ]);
        $customData = [
            'name' => 'Custom Name Override',
            'age' => 25,
        ];

        $result = $this->builder
            ->setRequest($request)
            ->setData($customData)
            ->build();

        // Assert: Custom data 'name' overrides the request 'name'
        $this->assertEquals([
            'name' => 'Custom Name Override',
            'email' => 'request@test.com',
            'age' => 25,
        ], $result);
    }

    public function test_it_returns_empty_array_with_no_input(): void
    {
        $result = $this->builder->build();
        $this->assertEquals([], $result);
    }
}

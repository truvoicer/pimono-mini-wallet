<?php

namespace App\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use App\Repositories\Builders\DataBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use App\Traits\Database\PaginationTrait;

class BaseRepository
{
    use PaginationTrait;

    protected DataBuilder $dataBuilder;

    protected Relation|Builder|QueryBuilder $query;

    protected static string $modelClass;

    public function __construct(
        protected Model $model
    ) {
        $this->dataBuilder = new DataBuilder($model);
        $this->initializeQuery();
    }

    public static function make(): self
    {
        if (!isset(static::$modelClass)) {
            throw new InvalidArgumentException("Model class must be defined to create repository instance.");
        }
        if (!class_exists(static::$modelClass)) {
            throw new InvalidArgumentException("Model class '" . static::$modelClass . "' does not exist.");
        }
        $model = new static::$modelClass();
        return new self($model);
    }

    public function initializeQuery(): self
    {
        $this->query = $this->model->newQuery();
        return $this;
    }

    public function getQuery(): Relation|Builder|QueryBuilder
    {
        return $this->query;
    }

    public function setQuery(Relation|Builder|QueryBuilder $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function getDataBuilder(): DataBuilder
    {
        return $this->dataBuilder;
    }

    public function insert(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    public function bulkDelete(array $ids, ?callable $queryCallback = null): int
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('IDs must be a non-empty array.');
        }
        if ($queryCallback) {
            $deleteQuery = $queryCallback($this->query);
        } else {
            $deleteQuery = $this->query
                ->whereIn('id', $ids);
        }
        $deletedCount = $deleteQuery->delete();
        if ($deletedCount === 0) {
            throw new \RuntimeException('No records were deleted.');
        }
        return $deletedCount;
    }

    public function paginate()
    {
        return $this->query->paginate(
            $this->perPage,
            ['*'],
            'page',
            (isset($this->page) ? $this->page : null)
        );
    }
}

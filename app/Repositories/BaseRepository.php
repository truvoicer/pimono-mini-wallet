<?php

namespace App\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use App\Repositories\Builders\DataBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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

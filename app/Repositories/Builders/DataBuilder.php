<?php

namespace App\Repositories\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class DataBuilder
{

    private Request $request;
    private array $ignore = [];
    private array $data = [];

    public function __construct(
        protected Model $model
    ) {
    }

    public function setRequest(Request|FacadesRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function setIgnore(array $ignore): self
    {
        $this->ignore = $ignore;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function build(): array {
        $requestData = [];
        if (isset($this->request)) {
            if ($this->request instanceof FormRequest) {
                $requestData = $this->request->validated();
            } else {
                $requestData = $this->request->all();
            }
        }
        if (count($this->ignore)) {
            foreach ($this->ignore as $field) {
                unset($requestData[$field]);
            }
        }

        return array_intersect_key(
            [...$requestData, ...$this->data],
            array_flip($this->model->getFillable())
        );
    }
}

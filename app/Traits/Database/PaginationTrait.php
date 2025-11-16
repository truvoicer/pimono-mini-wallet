<?php

namespace App\Traits\Database;

trait PaginationTrait
{
    public bool $paginate = false;
    public int $perPage = 10;
    public int $page;
    public int $total = 0;

    public function setPagination(bool $paginate = false): void
    {
        $this->paginate = $paginate;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

}

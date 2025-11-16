<?php
namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepository extends BaseRepository
{
    protected static string $modelClass = Currency::class;

    public function __construct()
    {
        parent::__construct(new $this::$modelClass());
    }
}

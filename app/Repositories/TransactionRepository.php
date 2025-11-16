<?php
namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    protected static string $modelClass = Transaction::class;

    public function __construct()
    {
        parent::__construct(new $this::$modelClass());
    }
}

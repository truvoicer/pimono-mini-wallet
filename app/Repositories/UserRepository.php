<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    protected static string $modelClass = User::class;

    public function __construct()
    {
        parent::__construct(new $this::$modelClass());
    }
}

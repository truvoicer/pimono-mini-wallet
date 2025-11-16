<?php
namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends BaseRepository
{
    protected static string $modelClass = Role::class;

    public function __construct()
    {
        parent::__construct(new $this::$modelClass());
    }
}

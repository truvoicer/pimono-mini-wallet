<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    protected static string $modelClass = Setting::class;

    public function __construct()
    {
        parent::__construct(new $this::$modelClass());
    }

}

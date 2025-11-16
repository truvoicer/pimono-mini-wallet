<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'description',
    ];

    protected static function newFactory()
    {
        return RoleFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'role_users',
        )->withTimestamps();
    }

}

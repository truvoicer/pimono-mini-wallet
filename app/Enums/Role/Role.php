<?php

namespace App\Enums\Role;

enum Role: string
{
    case GUEST = 'guest';
    case SUPERUSER = 'superuser';
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::SUPERUSER => 'Super User',
            self::ADMIN => 'Admin',
            self::USER => 'User',
            self::GUEST => 'Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPERUSER => 'Super User with all permissions',
            self::ADMIN => 'Admin User with elevated permissions',
            self::USER => 'Regular User with standard permissions',
            self::GUEST => 'Guest User with limited access',
        };
    }
}

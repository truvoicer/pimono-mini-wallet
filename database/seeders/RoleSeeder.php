<?php

namespace Database\Seeders;

use App\Enums\Role\Role;
use Illuminate\Database\Seeder;
use App\Models\Role as ModelsRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            $findRole = ModelsRole::where('name', $role->value)->first();
            if ($findRole instanceof ModelsRole && $findRole->exists) {
                $findRole->update([
                    'label' => $role->label(),
                    'description' => $role->description(),
                ]);
            } else {
                $createRole = ModelsRole::create([
                    'name' => $role->value,
                    'label' => $role->label(),
                    'description' => $role->description(),
                ]);
            }
        }
    }
}

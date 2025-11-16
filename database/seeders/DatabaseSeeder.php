<?php

namespace Database\Seeders;

use App\Enums\Role\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
            CurrencySeeder::class,
            SettingSeeder::class,
        ]);
        User::factory(5)->create();
        foreach ($this->testUsers() as $testUser) {
            $user = User::where('email', $testUser['email'])->first();
            if (!($user instanceof User && $user->exists)) {
                $user = User::factory()->create([
                    'name' => $testUser['name'],
                    'email' => $testUser['email'],
                    'password' => $testUser['password'],
                    'balance' => 5000.00,
                ]);

                $user->assignRole($testUser['role']);
            }
        }

        $this->call([
            TransactionSeeder::class
        ]);

    }

    public function testUsers(): array {
        return [
            [
                'name' => 'Super User',
                'email' => 'super@example.com',
                'password' => 'password', // password
                'role' => Role::SUPERUSER,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password', // password
                'role' => Role::ADMIN,
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => 'password', // password
                'role' => Role::USER,
            ],
            [
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'password' => 'password', // password
                'role' => Role::GUEST,
            ],
        ];
    }
}

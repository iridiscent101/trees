<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Create the initial administrator and normal user accounts.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Administrator',
                'password' => '123456789',
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Normal User',
                'password' => '123456789',
                'email_verified_at' => now(),
            ],
        );
    }
}

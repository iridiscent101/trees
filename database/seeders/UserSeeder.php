<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Create the initial administrator and normal user accounts.
     */
    public function run(): void
    {
        $password = env('SEED_PASSWORD');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('SEED_PASSWORD must be set before seeding user accounts.');
        }

        foreach ([
            ['admin@test.com', 'System Administrator', RoleName::Administrator],
            ['user@test.com', 'Normal User', RoleName::User],
        ] as [$email, $name, $role]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => $password,
            ]);

            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->syncRoles([$role->value]);
        }
    }
}

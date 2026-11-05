<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_verified_admin_and_regular_users_without_resetting_passwords(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);

        $administrator = User::where('email', 'admin@test.com')->firstOrFail();
        $regularUser = User::where('email', 'user@test.com')->firstOrFail();

        $this->assertTrue($administrator->hasRole(RoleName::Administrator->value));
        $this->assertTrue($regularUser->hasRole(RoleName::User->value));
        $this->assertNotNull($administrator->email_verified_at);
        $this->assertNotNull($regularUser->email_verified_at);
        $this->assertTrue(Hash::check('test-password', $administrator->password));
        $this->assertTrue(Hash::check('test-password', $regularUser->password));

        $administrator->update(['password' => 'changed-password']);
        $this->seed(UserSeeder::class);

        $this->assertSame(2, User::count());
        $this->assertTrue(Hash::check('changed-password', $administrator->fresh()->password));
    }

    public function test_it_rejects_a_missing_seed_password(): void
    {
        $previousPassword = $_ENV['SEED_PASSWORD'] ?? null;
        $_ENV['SEED_PASSWORD'] = '';

        try {
            $this->expectException(RuntimeException::class);
            $this->seed(UserSeeder::class);
        } finally {
            if ($previousPassword === null) {
                unset($_ENV['SEED_PASSWORD']);
            } else {
                $_ENV['SEED_PASSWORD'] = $previousPassword;
            }
        }
    }
}

<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_panel_redirects_guests_to_the_branded_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_filament_does_not_register_a_second_login_page(): void
    {
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_admin_panel_loads_the_custom_theme(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);

        $this->actingAs($administrator)
            ->get('/admin')
            ->assertOk()
            ->assertSee('resources/css/filament/admin/theme.css')
            ->assertSee('height: 2.75rem;', escape: false)
            ->assertSee('NVSU Tree Inventory')
            ->assertSee('QR Code-Integrated Monitoring System');
    }
}

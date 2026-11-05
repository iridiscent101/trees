<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_is_available(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Explore the', 'Trees', 'of NVSU'])
            ->assertSee('Featured Trees')
            ->assertSee('How It Works')
            ->assertSee('Recorded Species Labels')
            ->assertSee('Campus Tree Map')
            ->assertSee('Staff Login')
            ->assertDontSee('Guest Account')
            ->assertDontSee('>Guest<', false)
            ->assertSee('navbar sticky top-0', false)
            ->assertSee('nvsu-section relative z-10', false)
            ->assertDontSee('nvsu-section -mt-8 relative z-10', false)
            ->assertSee('home-hero-stage', false)
            ->assertSee('home-float-chip-map', false)
            ->assertSee('home-float-chip-verified', false)
            ->assertSee('home-float-chip-qr', false)
            ->assertSee('home-stat-grid', false)
            ->assertSee('home-tilt-card', false)
            ->assertSee('home-process-step', false)
            ->assertSee('btn btn-primary', false)
            ->assertSee('images/map.png', false)
            ->assertSee('card image-full', false)
            ->assertDontSee('id="tree-search"', false)
            ->assertDontSee('<form class="join', false)
            ->assertDontSee('css/home.css', false);
    }

    public function test_home_page_uses_https_assets_behind_a_proxy(): void
    {
        $this->withServerVariables([
            'HTTP_X_FORWARDED_HOST' => 'untrusted.example.test',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.0.0.1',
        ])->get('http://trees.example.test/')
            ->assertOk()
            ->assertSee('https://trees.example.test/build/assets/app-', false)
            ->assertSee('https://trees.example.test/images/tree.png', false);
    }

    public function test_about_page_uses_live_inventory_statistics(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Mapped records')
            ->assertDontSee('1,248');
    }

    public function test_authenticated_public_header_uses_a_circular_profile_trigger(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create([
            'name' => 'Normal User',
            'email' => 'normal.user@example.com',
        ]);
        $user->assignRole(RoleName::User->value);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('data-testid="account-avatar-trigger"', false)
            ->assertSee('btn btn-circle relative', false)
            ->assertSee('aria-label="Open account menu for Normal User"', false)
            ->assertSee('normal.user@example.com')
            ->assertSee('User');
    }
}

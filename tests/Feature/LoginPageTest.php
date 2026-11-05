<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome Back!')
            ->assertSee('Forgot Password?')
            ->assertSee('Secure access for authorized Nueva Vizcaya State University users.')
            ->assertSee('images/tree.png', false)
            ->assertSee('QR-TIMS logo')
            ->assertSee('images/nvsu.gif', false)
            ->assertSee('Nueva Vizcaya State University logo')
            ->assertSee('js/login.js', false)
            ->assertDontSee('Login with Local Network Account')
            ->assertDontSee('accessible within the local network');
    }

    public function test_user_can_request_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.submit'), ['email' => 'missing@example.com', 'password' => 'incorrect']);
        }

        $this->post(route('login.submit'), ['email' => 'missing@example.com', 'password' => 'incorrect'])->assertTooManyRequests();
    }
}

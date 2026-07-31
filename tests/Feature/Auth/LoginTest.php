<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('user@example.com|127.0.0.1');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee(__('auth.login'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        // A plain user holds no permissions, so login now routes them to their
        // account — staff-vs-customer routing is covered in RegistrationTest.
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-horse'),
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'correct-horse',
        ])->assertRedirect(route('account.dashboard', ['locale' => 'en']));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-horse'),
        ]);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-horse'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'correct-horse',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-horse'),
        ]);

        foreach (range(1, 5) as $ignored) {
            $this->post('/login', [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // The 6th attempt must be blocked by the throttle, even with the
        // correct password — proving the limiter, not just the credentials.
        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'correct-horse',
        ])->assertStatus(429);

        $this->assertGuest();
    }

    public function test_successful_login_clears_the_rate_limiter(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-horse'),
            'email_verified_at' => now(),
        ]);

        foreach (range(1, 3) as $ignored) {
            $this->post('/login', ['email' => 'user@example.com', 'password' => 'nope']);
        }

        $this->post('/login', ['email' => 'user@example.com', 'password' => 'correct-horse']);
        $this->assertAuthenticated();

        $this->assertSame(0, RateLimiter::attempts('user@example.com|127.0.0.1'));
    }

    public function test_authenticated_user_cannot_visit_login_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->get('/login')->assertRedirect();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}

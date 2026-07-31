<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('staff@example.com|127.0.0.1');
        RateLimiter::clear('customer@example.com|127.0.0.1');
    }

    private function staff(): User
    {
        $user = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('secret-pass'),
            'email_verified_at' => now(),
        ]);
        $user->givePermissionTo(Permission::findOrCreate('tours.view', 'web')->name);

        return $user;
    }

    public function test_the_admin_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(__('auth.admin_login'));
    }

    public function test_staff_can_sign_in_and_land_on_the_dashboard(): void
    {
        $this->staff();

        $this->post('/admin/login', ['email' => 'staff@example.com', 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    /**
     * The whole point of a separate door: a valid customer account is refused
     * here rather than bounced to their account or dropped on a 403.
     */
    public function test_a_customer_is_refused_at_the_admin_door(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('secret-pass'),
            'email_verified_at' => now(),
        ]);

        $this->post('/admin/login', ['email' => 'customer@example.com', 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');

        // And they are not left half-logged-in.
        $this->assertGuest();
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $this->staff();

        $this->post('/admin/login', ['email' => 'staff@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $this->staff();

        foreach (range(1, 5) as $ignored) {
            $this->post('/admin/login', ['email' => 'staff@example.com', 'password' => 'wrong']);
        }

        // The 6th attempt is blocked even with the correct password.
        $this->post('/admin/login', ['email' => 'staff@example.com', 'password' => 'secret-pass'])
            ->assertStatus(429);
    }
}

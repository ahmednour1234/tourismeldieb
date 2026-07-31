<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_registration_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee(__('auth.register'));
    }

    public function test_a_customer_can_register_and_is_logged_in(): void
    {
        $this->post('/register', [
            'name' => 'Nadia Farouk',
            'email' => 'nadia@example.com',
            'password' => 'a-good-password',
            'password_confirmation' => 'a-good-password',
        ])->assertRedirect(route('account.dashboard', ['locale' => 'en']));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'nadia@example.com', 'is_active' => true]);
    }

    /**
     * The public form makes customers, never staff. A registrant must not be
     * able to reach the admin just by signing up.
     */
    public function test_a_registered_user_is_not_staff(): void
    {
        $this->post('/register', [
            'name' => 'Nadia',
            'email' => 'nadia@example.com',
            'password' => 'a-good-password',
            'password_confirmation' => 'a-good-password',
        ]);

        $user = User::query()->where('email', 'nadia@example.com')->firstOrFail();

        $this->assertFalse($user->isStaff());
        $this->get('/admin')->assertRedirect(route('account.dashboard', ['locale' => 'en']));
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'a-good-password',
            'password_confirmation' => 'a-good-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $this->post('/register', [
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'password' => 'a-good-password',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    // ---- login routing -------------------------------------------------

    public function test_a_customer_login_lands_on_the_account(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('secret-pass'),
            'email_verified_at' => now(),
        ]);

        $this->post('/login', ['email' => 'customer@example.com', 'password' => 'secret-pass'])
            ->assertRedirect(route('account.dashboard', ['locale' => 'en']));
    }

    public function test_a_staff_login_lands_on_the_dashboard(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('secret-pass'),
            'email_verified_at' => now(),
        ]);
        $staff->givePermissionTo(Permission::findOrCreate('tours.view', 'web')->name);

        $this->post('/login', ['email' => 'staff@example.com', 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_customer_is_redirected_away_from_admin_not_403(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertRedirect(route('account.dashboard', ['locale' => 'en']));

        // And a staff member is let through.
        $staff = User::factory()->create(['email_verified_at' => now()]);
        $staff->assignRole(Role::findOrCreate('admin', 'web'));

        $this->actingAs($staff)->get('/admin')->assertOk();
    }

    public function test_a_logged_in_customer_hitting_login_goes_to_the_account(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($customer)
            ->get('/login')
            ->assertRedirect(route('account.dashboard', ['locale' => 'en']));
    }
}

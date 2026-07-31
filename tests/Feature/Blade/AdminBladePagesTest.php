<?php

declare(strict_types=1);

namespace Tests\Feature\Blade;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class AdminBladePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_a_customer_is_redirected_out_of_the_admin(): void
    {
        // No permissions at all → a customer. The staff gate bounces them to
        // their account rather than showing a dead-end 403.
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/admin/tours')
            ->assertRedirect(route('account.dashboard', ['locale' => 'en']));
    }

    public function test_staff_without_the_resource_permission_are_forbidden(): void
    {
        // Staff (they hold *a* permission) but not tours.view — this is the
        // per-resource authorization, distinct from the coarse staff gate.
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::findOrCreate('currencies.view'));

        $this->actingAs($user)->get('/admin/tours')->assertForbidden();
    }

    public function test_tour_manager_can_access_tour_management(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Permission::findOrCreate('tours.view');
        $user->givePermissionTo('tours.view');

        $this->actingAs($user)
            ->get('/admin/tours')
            ->assertOk()
            ->assertSee(__('admin.resources.tours'));
    }

    public function test_tour_form_displays_active_language_tabs(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        // The create form is gated on `tours.create`, not `tours.view`: a
        // read-only role must not be able to open it at all.
        Permission::findOrCreate('tours.create');
        $user->givePermissionTo('tours.create');

        // The tabs are rendered from the active languages, so they must exist.
        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'sort_order' => 1]);
        Language::query()->create(['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'sort_order' => 2]);

        $this->actingAs($user)
            ->get('/admin/tours/create')
            ->assertOk()
            ->assertSee('English')
            ->assertSee('العربية');
    }
}

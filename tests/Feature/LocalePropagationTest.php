<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The auth pages and the whole /admin group live outside the {locale} route
 * prefix. Without ApplySessionLocale they always rendered in the default
 * locale, ignoring the language the visitor had chosen.
 */
final class LocalePropagationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_the_locale_chosen_on_the_public_site(): void
    {
        $this->get('/ar')->assertOk();

        $this->get('/login')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee(__('auth.login', [], 'ar'));
    }

    public function test_login_page_falls_back_to_the_default_locale(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('dir="ltr"', false);
    }

    public function test_admin_dashboard_uses_the_session_locale(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->get('/ar');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_locale_prefix_still_overrides_the_session(): void
    {
        $this->get('/ar')->assertOk();

        // An explicit /en URL must win over the remembered Arabic session.
        $this->get('/en')
            ->assertOk()
            ->assertSee('dir="ltr"', false);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->get('/de')->assertNotFound();
    }

    public function test_admin_resource_page_uses_the_session_locale(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Permission::findOrCreate('tours.view');
        $user->givePermissionTo('tours.view');

        $this->get('/ar');

        $this->actingAs($user)
            ->get('/admin/tours')
            ->assertOk()
            ->assertSee(__('admin.resources.tours', [], 'ar'));
    }
}

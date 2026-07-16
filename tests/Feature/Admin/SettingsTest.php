<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use App\Services\Support\UiSettingsService;
use App\Shared\Contracts\SettingRepositoryContract;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 1]);
        Language::query()->create(['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 2]);
    }

    /**
     * @param  list<string>  $abilities
     */
    private function userWith(array $abilities): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        foreach ($abilities as $ability) {
            Permission::findOrCreate($ability, 'web');
        }

        $user->givePermissionTo($abilities);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Hurgada guide',
            'company_description' => ['en' => 'Day tours.', 'ar' => 'رحلات يومية.'],
            'company_address' => ['en' => 'Hurghada', 'ar' => 'الغردقة'],
            'contact_phone' => '+20 100 000 0000',
            'contact_whatsapp' => '+201000000000',
            'contact_email' => 'hello@example.com',
            'social_facebook' => null,
            'social_instagram' => null,
            'social_youtube' => null,
        ], $overrides);
    }

    public function test_the_settings_page_renders(): void
    {
        $this->actingAs($this->userWith(['settings.view']))
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee(__('admin.settings.groups.company'))
            ->assertSee(__('admin.settings.fields.contact_whatsapp'));
    }

    public function test_settings_are_saved(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload(['company_name' => 'New Name']))
            ->assertRedirect(route('admin.settings.index'));

        $this->assertSame('New Name', app(SettingRepositoryContract::class)->get('company_name'));
    }

    public function test_a_translatable_setting_stores_every_locale(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload([
                'company_address' => ['en' => 'Sheraton Road', 'ar' => 'شارع شيراتون'],
            ]));

        $repository = app(SettingRepositoryContract::class);

        app()->setLocale('en');
        $this->assertSame('Sheraton Road', $repository->get('company_address'));

        app()->setLocale('ar');
        $this->assertSame('شارع شيراتون', $repository->get('company_address'));
    }

    public function test_a_missing_translation_falls_back_to_the_fallback_locale(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload([
                'company_address' => ['en' => 'Sheraton Road', 'ar' => null],
            ]));

        app()->setLocale('ar');

        $this->assertSame('Sheraton Road', app(SettingRepositoryContract::class)->get('company_address'));
    }

    public function test_an_unsaved_setting_falls_back_to_its_schema_default(): void
    {
        $this->assertSame('Hurgada guide', app(SettingRepositoryContract::class)->get('company_name'));
    }

    /**
     * The whole table is cached, so a write that does not bust the cache leaves
     * the public site showing the previous values indefinitely.
     */
    public function test_saving_busts_the_cache(): void
    {
        $repository = app(SettingRepositoryContract::class);
        $repository->get('company_name');

        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload(['company_name' => 'Changed']));

        $this->assertSame('Changed', $repository->get('company_name'));
    }

    public function test_the_public_site_renders_saved_settings(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload([
                'contact_phone' => '+20 155 555 1234',
                'social_facebook' => 'https://facebook.com/example',
            ]));

        $this->get('/en')
            ->assertOk()
            ->assertSee('+20 155 555 1234')
            ->assertSee('https://facebook.com/example');
    }

    public function test_blank_social_links_are_not_rendered(): void
    {
        $company = app(UiSettingsService::class)->company();

        $this->assertSame([], $company['social'], 'unset social links must be omitted, not rendered as dead "#" hrefs');
    }

    public function test_validation_rejects_a_bad_email(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload(['contact_email' => 'not-an-email']))
            ->assertSessionHasErrors('contact_email');
    }

    public function test_validation_rejects_a_bad_url(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload(['social_facebook' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('social_facebook');
    }

    public function test_view_permission_alone_cannot_save(): void
    {
        $this->actingAs($this->userWith(['settings.view']))
            ->put('/admin/settings', $this->validPayload(['company_name' => 'Hijacked']))
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'company_name']);
    }

    public function test_unknown_keys_are_ignored(): void
    {
        $this->actingAs($this->userWith(['settings.update']))
            ->put('/admin/settings', $this->validPayload(['not_a_real_setting' => 'x']));

        $this->assertDatabaseMissing('settings', ['key' => 'not_a_real_setting']);
    }

    public function test_a_save_is_recorded_in_the_activity_log(): void
    {
        $user = $this->userWith(['settings.update']);

        $this->actingAs($user)->put('/admin/settings', $this->validPayload(['company_name' => 'Logged']));

        $activity = Activity::latest()->first();

        $this->assertNotNull($activity);
        $this->assertSame('updated', $activity->event);
        $this->assertSame($user->id, $activity->causer_id);
        $this->assertContains('company_name', $activity->properties['keys']);
    }

    public function test_an_unchanged_save_is_not_logged(): void
    {
        $user = $this->userWith(['settings.update']);

        $this->actingAs($user)->put('/admin/settings', $this->validPayload());
        Activity::query()->delete();

        // Saving the identical payload again must not manufacture an audit
        // entry for a change that did not happen.
        $this->actingAs($user)->put('/admin/settings', $this->validPayload());

        $this->assertSame(0, Activity::query()->count());
    }

    public function test_the_activity_log_records_which_keys_changed_but_not_their_values(): void
    {
        $user = $this->userWith(['settings.update']);

        $this->actingAs($user)->put('/admin/settings', $this->validPayload(['contact_email' => 'secret@example.com']));

        $properties = Activity::latest()->first()?->properties->toArray() ?? [];

        $this->assertArrayHasKey('keys', $properties);
        $this->assertStringNotContainsString('secret@example.com', json_encode($properties));
    }

    public function test_re_seeding_does_not_overwrite_an_edited_value(): void
    {
        Setting::query()->create([
            'group' => 'company',
            'key' => 'company_name',
            'value' => 'Edited By Admin',
            'is_translatable' => false,
        ]);

        $this->seed(SettingSeeder::class);

        $this->assertSame('Edited By Admin', app(SettingRepositoryContract::class)->get('company_name'));
    }
}

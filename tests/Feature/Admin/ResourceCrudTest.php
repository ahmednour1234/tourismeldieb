<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

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

    private function language(array $overrides = []): Language
    {
        return Language::create(array_merge([
            'code' => 'en', 'name' => 'English', 'native_name' => 'English',
            'direction' => 'ltr', 'is_active' => true, 'sort_order' => 0,
        ], $overrides));
    }

    public function test_a_record_can_be_created(): void
    {
        $user = $this->userWith(['languages.create']);

        $this->actingAs($user)->post('/admin/languages', [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'sort_order' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('admin.languages.index'));

        $this->assertDatabaseHas('languages', ['code' => 'de', 'name' => 'German']);
    }

    /**
     * `resource` is a route default, so Laravel binds it *after* the URI
     * parameters: the controller receives [id, resource]. A signature ordered
     * (resource, id) silently receives the id as the resource name and 404s
     * every edit page, while index/create keep working.
     */
    public function test_edit_show_and_update_routes_bind_their_parameters_correctly(): void
    {
        $user = $this->userWith(['languages.view', 'languages.update']);
        $language = $this->language();

        $this->actingAs($user)->get("/admin/languages/{$language->id}")->assertOk();
        $this->actingAs($user)->get("/admin/languages/{$language->id}/edit")->assertOk();
    }

    public function test_a_record_can_be_updated(): void
    {
        $user = $this->userWith(['languages.update']);
        $language = $this->language();

        $this->actingAs($user)->put("/admin/languages/{$language->id}", [
            'code' => 'en',
            'name' => 'English (edited)',
            'native_name' => 'English',
            'direction' => 'ltr',
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect(route('admin.languages.index'));

        $this->assertSame('English (edited)', $language->fresh()->name);
    }

    /**
     * An unchecked checkbox posts nothing at all. Without the hidden "0"
     * companion field the key never reaches the request, and a toggle could be
     * switched on but never off.
     */
    public function test_a_toggle_can_be_switched_off(): void
    {
        $user = $this->userWith(['languages.update']);
        $language = $this->language(['is_active' => true]);

        $this->actingAs($user)->put("/admin/languages/{$language->id}", [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'sort_order' => 0,
            'is_active' => '0',
        ]);

        $this->assertFalse($language->fresh()->is_active);
    }

    public function test_save_and_continue_returns_to_the_form(): void
    {
        $user = $this->userWith(['languages.create']);

        $response = $this->actingAs($user)->post('/admin/languages', [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'sort_order' => 0,
            'is_active' => '1',
            'continue' => '1',
        ]);

        $id = Language::where('code', 'de')->value('id');
        $response->assertRedirect(route('admin.languages.edit', $id));
    }

    public function test_a_record_is_soft_deleted_not_destroyed(): void
    {
        $user = $this->userWith(['languages.delete']);
        $this->language();
        $target = $this->language(['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch']);

        $this->actingAs($user)
            ->delete("/admin/languages/{$target->id}")
            ->assertRedirect(route('admin.languages.index'));

        $this->assertSoftDeleted('languages', ['id' => $target->id]);
    }

    public function test_writes_are_recorded_in_the_activity_log(): void
    {
        $user = $this->userWith(['languages.create']);

        $this->actingAs($user)->post('/admin/languages', [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'sort_order' => 0,
            'is_active' => '1',
        ]);

        $activity = Activity::latest()->first();

        $this->assertNotNull($activity);
        $this->assertSame('created', $activity->event);
        $this->assertSame($user->id, $activity->causer_id);
    }

    public function test_validation_rejects_a_duplicate_code(): void
    {
        $user = $this->userWith(['languages.create']);
        $this->language();

        $this->actingAs($user)->post('/admin/languages', [
            'code' => 'en',
            'name' => 'Another English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertSessionHasErrors('code');

        $this->assertSame(1, Language::where('code', 'en')->count());
    }

    // ---- authorization -------------------------------------------------

    public function test_view_permission_alone_cannot_open_the_create_form(): void
    {
        $user = $this->userWith(['languages.view']);

        $this->actingAs($user)->get('/admin/languages/create')->assertForbidden();
    }

    public function test_view_permission_alone_cannot_store(): void
    {
        $user = $this->userWith(['languages.view']);

        $this->actingAs($user)->post('/admin/languages', [
            'code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch',
            'direction' => 'ltr', 'sort_order' => 0, 'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing('languages', ['code' => 'de']);
    }

    public function test_view_permission_alone_cannot_delete(): void
    {
        $user = $this->userWith(['languages.view']);
        $language = $this->language();

        $this->actingAs($user)->delete("/admin/languages/{$language->id}")->assertForbidden();
        $this->assertDatabaseHas('languages', ['id' => $language->id, 'deleted_at' => null]);
    }

    // ---- domain guards -------------------------------------------------

    public function test_the_last_active_language_cannot_be_deleted(): void
    {
        $user = $this->userWith(['languages.delete']);
        $language = $this->language();

        $this->actingAs($user)
            ->delete("/admin/languages/{$language->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('languages', ['id' => $language->id, 'deleted_at' => null]);
    }

    public function test_the_default_currency_cannot_be_deleted(): void
    {
        $user = $this->userWith(['currencies.delete']);

        $currency = Currency::create([
            'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
            'decimal_places' => 2, 'is_default' => true, 'is_active' => true, 'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->delete("/admin/currencies/{$currency->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('currencies', ['id' => $currency->id, 'deleted_at' => null]);
    }

    public function test_only_one_currency_can_be_the_default(): void
    {
        $user = $this->userWith(['currencies.create']);

        $usd = Currency::create([
            'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
            'decimal_places' => 2, 'is_default' => true, 'is_active' => true, 'sort_order' => 0,
        ]);

        $this->actingAs($user)->post('/admin/currencies', [
            'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€',
            'decimal_places' => 2, 'is_default' => '1', 'is_active' => '1', 'sort_order' => 1,
        ]);

        $this->assertFalse($usd->fresh()->is_default, 'the previous default must be unset');
        $this->assertSame(1, Currency::where('is_default', true)->count());
    }

    // ---- users ---------------------------------------------------------

    public function test_a_blank_password_leaves_the_existing_one_untouched(): void
    {
        $admin = $this->userWith(['users.update']);
        $target = User::factory()->create(['password' => Hash::make('original-password')]);
        $hash = $target->password;

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'Renamed',
            'email' => $target->email,
            'password' => '',
            'is_active' => '1',
        ]);

        $this->assertSame($hash, $target->fresh()->password, 'a blank password must not clear the hash');
        $this->assertTrue(Hash::check('original-password', $target->fresh()->password));
    }

    public function test_a_supplied_password_is_hashed_once(): void
    {
        $admin = $this->userWith(['users.update']);
        $target = User::factory()->create(['password' => Hash::make('original-password')]);

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'a-brand-new-password',
            'is_active' => '1',
        ]);

        $this->assertTrue(
            Hash::check('a-brand-new-password', $target->fresh()->password),
            'the new password must be usable — not double-hashed',
        );
    }
}

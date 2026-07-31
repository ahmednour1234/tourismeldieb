<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\BookingRequest;
use App\Models\NewsletterSubscription;
use App\Models\Tour;
use App\Models\User;
use Database\Seeders\TourismCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TourismCatalogSeeder::class);
    }

    private function booking(User $user, array $overrides = []): BookingRequest
    {
        return BookingRequest::query()->create(array_merge([
            'reference' => BookingRequest::generateReference(),
            'tour_id' => Tour::query()->where('code', 'orange-bay')->value('id'),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'preferred_date' => Carbon::today()->addWeek(),
            'adults' => 2,
            'status' => 'confirmed',
        ], $overrides));
    }

    public function test_the_account_requires_login(): void
    {
        $this->get('/en/account')->assertRedirect('/login');
    }

    public function test_the_dashboard_shows_the_customers_bookings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->booking($user);

        $this->actingAs($user)
            ->get('/en/account')
            ->assertOk()
            ->assertSee('Orange Bay Snorkeling')
            ->assertSee(__('website.booking.status.confirmed'));
    }

    /**
     * A customer must never see another customer's bookings.
     */
    public function test_a_customer_sees_only_their_own_bookings(): void
    {
        $mine = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);

        $this->booking($mine, ['customer_name' => 'Mine']);
        $this->booking($other, ['customer_name' => 'Theirs', 'reference' => 'HG-OTHER1']);

        $this->actingAs($mine)
            ->get('/en/account/bookings')
            ->assertOk()
            ->assertDontSee('HG-OTHER1');
    }

    public function test_a_customer_can_update_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email_verified_at' => now()]);

        $this->actingAs($user)
            ->put(route('account.profile.update', ['locale' => 'en']), [
                'name' => 'New Name',
                'email' => $user->email,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_changing_the_email_unverifies_it(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->put(route('account.profile.update', ['locale' => 'en']), [
            'name' => $user->name,
            'email' => 'changed@example.com',
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('the-real-password'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->put(route('account.profile.update', ['locale' => 'en']), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('the-real-password', $user->fresh()->password));
    }

    public function test_a_customer_cannot_take_another_users_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->put(route('account.profile.update', ['locale' => 'en']), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])->assertSessionHasErrors('email');
    }

    // ---- newsletter ----------------------------------------------------

    public function test_a_visitor_can_subscribe_to_the_newsletter(): void
    {
        $this->post(route('newsletter.subscribe', ['locale' => 'en']), ['email' => 'reader@example.com'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('newsletter_subscriptions', ['email' => 'reader@example.com']);
    }

    public function test_subscribing_twice_is_idempotent(): void
    {
        $this->post(route('newsletter.subscribe', ['locale' => 'en']), ['email' => 'reader@example.com']);
        $this->post(route('newsletter.subscribe', ['locale' => 'en']), ['email' => 'READER@example.com']);

        $this->assertSame(1, NewsletterSubscription::query()->count());
    }

    public function test_the_newsletter_rejects_a_bad_email(): void
    {
        $this->post(route('newsletter.subscribe', ['locale' => 'en']), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
    }
}

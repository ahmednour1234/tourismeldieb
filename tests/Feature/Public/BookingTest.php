<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Tour;
use App\Models\User;
use App\Notifications\BookingRequestReceived;
use App\Notifications\BookingRequestSubmitted;
use App\Shared\Contracts\SettingRepositoryContract;
use Database\Seeders\TourismCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

final class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TourismCatalogSeeder::class);
    }

    private function tour(): Tour
    {
        return Tour::query()->where('code', 'orange-bay')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tour_id' => $this->tour()->id,
            'customer_name' => 'Sarah Klein',
            'customer_email' => 'sarah@example.com',
            'customer_phone' => '+49 170 1234567',
            'preferred_date' => Carbon::today()->addWeeks(2)->toDateString(),
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'notes' => 'Staying at Sunrise Resort.',
        ], $overrides);
    }

    public function test_the_booking_form_renders(): void
    {
        $this->get('/en/book')
            ->assertOk()
            ->assertSee(__('website.booking.title'));
    }

    public function test_a_book_now_link_preselects_its_tour(): void
    {
        $this->get('/en/book?tour=orange-bay-snorkeling')
            ->assertOk()
            ->assertSee('Orange Bay Snorkeling');
    }

    public function test_a_booking_request_is_recorded(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload())
            ->assertRedirect(route('booking.confirmed', ['locale' => 'en']))
            ->assertSessionHas('booking_reference');

        $this->assertDatabaseHas('booking_requests', [
            'customer_email' => 'sarah@example.com',
            'adults' => 2,
            'children' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_the_dialling_code_is_joined_onto_the_number(): void
    {
        Notification::fake();

        // The trunk zero a German writes domestically is not part of the
        // international number, so it must not survive the join.
        $this->post('/en/book', $this->payload([
            'customer_phone_code' => '+49',
            'customer_phone' => '0170 1234567',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', [
            'customer_phone' => '+49 170 1234567',
        ]);
    }

    public function test_a_pasted_international_number_keeps_its_own_code(): void
    {
        Notification::fake();

        // Someone who pastes a full number should not get the select's default
        // code stuck on the front of a number that already has one.
        $this->post('/en/book', $this->payload([
            'customer_phone_code' => '+20',
            'customer_phone' => '+39 333 111222',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', [
            'customer_phone' => '+39 333 111222',
        ]);
    }

    public function test_a_code_with_no_number_stores_nothing(): void
    {
        Notification::fake();

        // The select always has something chosen, so a customer who leaves the
        // optional phone blank must not be recorded as reachable on "+49".
        $this->post('/en/book', $this->payload([
            'customer_phone_code' => '+49',
            'customer_phone' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', [
            'customer_email' => 'sarah@example.com',
            'customer_phone' => null,
        ]);
    }

    public function test_an_unoffered_dialling_code_is_rejected(): void
    {
        Notification::fake();

        // A <select> constrains a browser, not a client posting directly.
        $this->post('/en/book', $this->payload([
            'customer_phone_code' => '+999',
            'customer_phone' => '1234567',
        ]))->assertSessionHasErrors('customer_phone_code');

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_the_customer_is_emailed_a_confirmation(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());

        Notification::assertSentOnDemand(BookingRequestReceived::class);
    }

    public function test_the_operator_is_emailed_the_new_request(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());

        // Both halves go out: the customer's confirmation and the operator's
        // alert. Before the alert existed a request landed in the database and
        // nobody was told about it.
        Notification::assertSentOnDemand(
            BookingRequestSubmitted::class,
            function (BookingRequestSubmitted $notification, array $channels, object $notifiable): bool {
                return $notifiable->routes['mail'] === 'Tourshurgada@gmail.com';
            },
        );
    }

    public function test_the_operator_alert_address_follows_the_contact_setting(): void
    {
        Notification::fake();

        // The address is a setting, not a constant, so staff can redirect their
        // own alerts from the admin without a deploy.
        app(SettingRepositoryContract::class)->put(['contact_email' => 'ops@example.com']);

        $this->post('/en/book', $this->payload());

        Notification::assertSentOnDemand(
            BookingRequestSubmitted::class,
            fn (BookingRequestSubmitted $n, array $channels, object $notifiable): bool => $notifiable->routes['mail'] === 'ops@example.com',
        );
    }

    public function test_an_unusable_alert_address_still_books_and_still_confirms(): void
    {
        Notification::fake();

        // With no usable address the alert is skipped rather than thrown: the
        // booking is already committed, so an internal alerting problem must
        // not surface to the visitor as a failed booking.
        app(SettingRepositoryContract::class)->put(['contact_email' => 'not-an-email']);
        config(['mail.from.address' => 'not-an-email-either']);

        $this->post('/en/book', $this->payload())
            ->assertRedirect(route('booking.confirmed', ['locale' => 'en']));

        $this->assertDatabaseHas('booking_requests', [
            'customer_email' => 'sarah@example.com',
        ]);

        // The customer is still told we have their request.
        Notification::assertSentOnDemand(BookingRequestReceived::class);
        Notification::assertNothingSentTo(
            (new AnonymousNotifiable)->route('mail', 'not-an-email'),
        );
    }

    public function test_the_reference_is_shown_once_and_not_exposed_in_the_url(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());

        $reference = BookingRequest::query()->value('reference');

        $this->followingRedirects()
            ->post('/en/book', $this->payload(['customer_email' => 'second@example.com']))
            ->assertOk()
            ->assertSee('HG-');

        // Landing on the confirmation with no fresh submission must not leak
        // someone else's booking — it falls back to the form.
        $this->get('/en/book/confirmed')
            ->assertOk()
            ->assertDontSee($reference);
    }

    public function test_a_draft_tour_cannot_be_booked(): void
    {
        $tour = $this->tour();
        $tour->update(['status' => 'draft']);

        $this->post('/en/book', $this->payload(['tour_id' => $tour->id]))
            ->assertSessionHasErrors('tour_id');

        $this->assertSame(0, BookingRequest::query()->count());
    }

    public function test_a_booking_cannot_be_made_for_a_past_date(): void
    {
        $this->post('/en/book', $this->payload(['preferred_date' => Carbon::yesterday()->toDateString()]))
            ->assertSessionHasErrors('preferred_date');
    }

    public function test_a_booking_requires_at_least_one_adult(): void
    {
        $this->post('/en/book', $this->payload(['adults' => 0]))
            ->assertSessionHasErrors('adults');
    }

    public function test_an_absurd_group_size_is_rejected_before_it_reaches_the_database(): void
    {
        $this->post('/en/book', $this->payload(['adults' => 40, 'children' => 40, 'infants' => 20]))
            ->assertSessionHasErrors();

        $this->assertSame(0, BookingRequest::query()->count());
    }

    public function test_a_booking_by_a_signed_in_customer_is_linked_to_their_account(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post('/en/book', $this->payload());

        $this->assertSame($user->id, BookingRequest::query()->value('user_id'));
    }

    public function test_a_guest_booking_has_no_account(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());

        $this->assertNull(BookingRequest::query()->value('user_id'));
    }

    public function test_each_booking_gets_a_unique_reference(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());
        $this->post('/en/book', $this->payload(['customer_email' => 'other@example.com']));

        $this->assertSame(2, BookingRequest::query()->distinct()->count('reference'));
    }

    public function test_a_booking_is_recorded_in_the_activity_log(): void
    {
        Notification::fake();

        $this->post('/en/book', $this->payload());

        $activity = Activity::query()->where('log_name', 'bookings')->latest()->first();

        $this->assertNotNull($activity);
        $this->assertSame('created', $activity->event);
    }

    public function test_the_arabic_booking_form_renders_and_records_its_locale(): void
    {
        Notification::fake();

        $this->get('/ar/book')->assertOk()->assertSee('dir="rtl"', false);

        $this->post('/ar/book', $this->payload());

        $this->assertSame('ar', BookingRequest::query()->value('locale'));
    }

    // ---- contact form --------------------------------------------------

    public function test_a_contact_message_is_recorded(): void
    {
        $this->post('/en/contact', [
            'name' => 'Tom Reed',
            'email' => 'tom@example.com',
            'message' => 'Do you run private trips to Luxor?',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'tom@example.com',
            'status' => 'new',
        ]);
    }

    /**
     * The honeypot is hidden from people and irresistible to bots.
     */
    public function test_a_filled_honeypot_is_rejected(): void
    {
        $this->post('/en/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Buy cheap watches',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_the_contact_form_validates(): void
    {
        $this->post('/en/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
            ->assertSessionHasErrors(['name', 'email', 'message']);
    }
}

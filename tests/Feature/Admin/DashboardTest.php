<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\Language;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): Tour
    {
        $currency = Currency::create([
            'code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£',
            'decimal_places' => 2, 'is_default' => true, 'is_active' => true, 'sort_order' => 0,
        ]);

        $country = Country::create([
            'currency_id' => $currency->id, 'code' => 'EG',
            'name' => 'Egypt', 'phone_code' => '+20', 'is_active' => true,
        ]);

        $destination = Destination::create([
            'country_id' => $country->id, 'code' => 'hurghada',
            'timezone' => 'Africa/Cairo', 'is_active' => true, 'sort_order' => 0,
        ]);

        $category = TourCategory::create(['code' => 'sea-trips', 'is_active' => true, 'sort_order' => 0]);

        Language::create([
            'code' => 'en', 'name' => 'English', 'native_name' => 'English',
            'direction' => 'ltr', 'is_active' => true, 'sort_order' => 0,
        ]);

        $tour = Tour::create([
            'destination_id' => $destination->id,
            'tour_category_id' => $category->id,
            'code' => 'orange-bay',
            'status' => 'published',
            'sort_order' => 0,
        ]);

        $tour->translations()->create([
            'locale' => 'en', 'name' => 'Orange Bay Snorkeling', 'slug' => 'orange-bay',
        ]);

        return $tour;
    }

    private function admin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(
            collect(['tours', 'destinations', 'categories', 'countries', 'languages', 'currencies'])
                ->map(fn (string $r): string => Permission::findOrCreate($r.'.view')->name)
                ->all()
        );

        return $user;
    }

    public function test_dashboard_renders_with_real_counts(): void
    {
        $this->seedCatalog();

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('admin.dashboard.schedule_title'))
            ->assertSee(__('admin.stats.tours'));
    }

    public function test_stats_report_actual_row_counts(): void
    {
        $this->seedCatalog();

        $stats = collect(app(AdminDashboardService::class)->summary()['stats'])
            ->keyBy('label');

        $this->assertSame(1, $stats[__('admin.stats.tours')]['value']);
        $this->assertSame(1, $stats[__('admin.stats.destinations')]['value']);
        $this->assertSame(1, $stats[__('admin.stats.countries')]['value']);
    }

    public function test_schedule_returns_a_full_window_including_empty_days(): void
    {
        $tour = $this->seedCatalog();
        $option = $this->makeOption($tour);

        // Two departures on one day only; every other day must still appear.
        DB::table('tour_departures')->insert([
            $this->departureRow($option, Carbon::today()->addDay()->setTime(9, 0), 20),
            $this->departureRow($option, Carbon::today()->addDay()->setTime(14, 0), 12),
        ]);

        $schedule = app(AdminDashboardService::class)->summary()['schedule'];

        $this->assertCount(30, $schedule['days'], 'the window must be a full 30 days');
        $this->assertSame(32, $schedule['totalSeats']);
        $this->assertSame(2, $schedule['totalDepartures']);
        $this->assertSame(32, $schedule['peakSeats']);

        $empty = collect($schedule['days'])->firstWhere('date', Carbon::today()->toDateString());
        $this->assertSame(0, $empty['seats'], 'days without departures must be present as zeroes');
    }

    public function test_cancelled_departures_are_excluded(): void
    {
        $tour = $this->seedCatalog();
        $option = $this->makeOption($tour);

        DB::table('tour_departures')->insert([
            $this->departureRow($option, Carbon::today()->addDay()->setTime(9, 0), 20),
            $this->departureRow($option, Carbon::today()->addDay()->setTime(11, 0), 50, 'cancelled'),
        ]);

        $schedule = app(AdminDashboardService::class)->summary()['schedule'];

        $this->assertSame(20, $schedule['totalSeats'], 'cancelled departures must not count toward capacity');
        $this->assertSame(1, $schedule['totalDepartures']);
    }

    public function test_departures_outside_the_window_are_excluded(): void
    {
        $tour = $this->seedCatalog();
        $option = $this->makeOption($tour);

        DB::table('tour_departures')->insert([
            $this->departureRow($option, Carbon::today()->subDays(3)->setTime(9, 0), 99),
            $this->departureRow($option, Carbon::today()->addDays(60)->setTime(9, 0), 99),
        ]);

        $schedule = app(AdminDashboardService::class)->summary()['schedule'];

        $this->assertSame(0, $schedule['totalSeats'], 'past and far-future departures must be excluded');
    }

    public function test_incomplete_tours_are_flagged_when_a_translation_is_missing(): void
    {
        $this->seedCatalog();

        // A second active language the tour has no translation for.
        Language::create([
            'code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية',
            'direction' => 'rtl', 'is_active' => true, 'sort_order' => 1,
        ]);

        $incomplete = app(AdminDashboardService::class)->summary()['incomplete'];

        $this->assertCount(1, $incomplete);
        $this->assertSame('Orange Bay Snorkeling', $incomplete[0]['name']);
        $this->assertStringContainsString('1', $incomplete[0]['reason']);
    }

    public function test_fully_translated_tours_are_not_flagged(): void
    {
        $this->seedCatalog();

        // Only English is active, and the tour has an English translation.
        $this->assertSame([], app(AdminDashboardService::class)->summary()['incomplete']);
    }

    private function makeOption(Tour $tour): int
    {
        return (int) DB::table('tour_options')->insertGetId([
            'tour_id' => $tour->id,
            'code' => 'standard',
            'capacity' => 30,
            'minimum_guests' => 1,
            'maximum_guests' => 10,
            'minimum_booking_quantity' => 1,
            'maximum_booking_quantity' => 10,
            'is_private' => false,
            'requires_manual_confirmation' => false,
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function departureRow(int $optionId, Carbon $at, int $capacity, string $status = 'available'): array
    {
        return [
            'tour_option_id' => $optionId,
            'departure_date' => $at->toDateString(),
            'start_datetime' => $at,
            'capacity' => $capacity,
            'reserved_capacity' => 0,
            'confirmed_capacity' => 0,
            'available_capacity' => $capacity,
            'status' => $status,
            'generated_automatically' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

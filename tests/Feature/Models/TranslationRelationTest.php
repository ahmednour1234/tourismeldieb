<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TranslationRelationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Builds one tour with EN + AR translations. Every unique-constrained
     * column is suffixed so repeated calls in one test do not collide.
     */
    private function makeTour(string $suffix = ''): Tour
    {
        $currency = Currency::firstOrCreate(
            ['code' => 'EGP'],
            [
                'name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimal_places' => 2,
                'is_default' => true, 'is_active' => true, 'sort_order' => 0,
            ],
        );

        $country = Country::firstOrCreate(
            ['code' => 'EG'],
            ['currency_id' => $currency->id, 'name' => 'Egypt', 'phone_code' => '+20', 'is_active' => true],
        );

        $destination = Destination::create([
            'country_id' => $country->id, 'code' => 'hurghada'.$suffix,
            'timezone' => 'Africa/Cairo', 'is_active' => true, 'sort_order' => 0,
        ]);

        $category = TourCategory::create([
            'code' => 'sea-trips'.$suffix, 'is_active' => true, 'sort_order' => 0,
        ]);

        $tour = Tour::create([
            'destination_id' => $destination->id,
            'tour_category_id' => $category->id,
            'code' => 'orange-bay'.$suffix,
            'status' => 'published',
            'sort_order' => 0,
        ]);

        $tour->translations()->createMany([
            ['locale' => 'en', 'name' => 'Orange Bay Snorkeling', 'slug' => 'orange-bay-snorkeling'.$suffix],
            ['locale' => 'ar', 'name' => 'سنوركلينج أورانج باي', 'slug' => 'orange-bay-ar'.$suffix],
        ]);

        return $tour;
    }

    public function test_translation_resolves_the_active_locale(): void
    {
        $this->makeTour();

        app()->setLocale('ar');

        $this->assertSame('سنوركلينج أورانج باي', Tour::with('translation')->first()?->translation?->name);
    }

    public function test_translation_falls_back_when_locale_is_missing(): void
    {
        $this->makeTour();

        app()->setLocale('fr');

        $this->assertSame('Orange Bay Snorkeling', Tour::with('translation')->first()?->translation?->name);
    }

    public function test_translation_is_eager_loaded_without_n_plus_one(): void
    {
        foreach (range(1, 3) as $i) {
            $this->makeTour('-'.$i);
        }

        app()->setLocale('en');

        DB::enableQueryLog();
        $tours = Tour::with('translation')->get();
        $tours->each(fn (Tour $tour) => $tour->translation?->name);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertGreaterThan(1, $tours->count());
        $this->assertLessThanOrEqual(2, $queries, 'translation() must eager load, not query per row');
    }
}

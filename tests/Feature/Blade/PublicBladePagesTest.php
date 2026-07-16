<?php

declare(strict_types=1);

namespace Tests\Feature\Blade;

use Database\Seeders\TourismCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicBladePagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The public layout renders the language and currency switchers from the
     * catalogue. These tests previously ran against a database with no tables
     * at all and passed only because those lists were hardcoded arrays.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // The catalogue seeder is the source of the destinations and tours
        // these pages assert on. They used to pass with an empty database
        // because PublicPageService fell back to hardcoded copies of this same
        // content — so a misconfigured database rendered a plausible site.
        $this->seed(TourismCatalogSeeder::class);
    }

    public function test_home_page_returns_blade_response(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Red Sea tours built around your day');
    }

    public function test_arabic_page_has_rtl_direction(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_published_destination_page_loads(): void
    {
        $this->get('/en/hurghada')
            ->assertOk()
            ->assertSee('Hurghada');
    }

    public function test_inactive_or_missing_destination_returns_404(): void
    {
        $this->get('/en/missing-destination')->assertNotFound();
    }

    public function test_published_tour_page_loads(): void
    {
        $this->get('/en/hurghada/tours/orange-bay-snorkeling')
            ->assertOk()
            ->assertSee('Orange Bay Snorkeling');
    }

    public function test_draft_tour_returns_404_publicly(): void
    {
        $this->get('/en/hurghada/tours/draft-tour')->assertNotFound();
    }

    public function test_tour_search_filters_use_query_strings(): void
    {
        $this->get('/en/tours?q=safari')
            ->assertOk()
            ->assertSee('Desert Safari Quad Bike');
    }

    public function test_no_public_page_returns_unintended_json(): void
    {
        $response = $this->get('/en/destinations');

        $response->assertOk();
        $this->assertStringNotContainsString('application/json', (string) $response->headers->get('content-type'));
    }
}

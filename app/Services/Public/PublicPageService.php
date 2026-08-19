<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Testimonial;
use App\Models\TourCategory;
use App\Services\Support\CurrencyConverter;
use App\Services\Support\SeoService;
use App\Services\Support\UiSettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class PublicPageService
{
    public function __construct(
        private readonly SeoService $seoService,
        private readonly UiSettingsService $settingsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function home(string $locale): array
    {
        return [
            'seo' => $this->seoService->page([
                'title' => __('website.home.meta_title'),
                'description' => __('website.home.meta_description'),
            ]),
            'settings' => $this->settingsService->company(),
            'destinations' => $this->destinations(),
            'featuredTours' => array_slice($this->tours(), 0, 3),
            'categories' => $this->categories(),
            'blogPosts' => $this->blogPosts(),
            'testimonials' => $this->testimonials(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function destinationIndex(string $locale): array
    {
        return [
            'seo' => $this->seoService->page(['title' => __('website.destinations.title')]),
            'destinations' => $this->destinations(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function destinationShow(string $locale, string $slug): array
    {
        $destination = collect($this->destinations())->firstWhere('slug', $slug);

        abort_if($destination === null || $destination['active'] === false, 404);

        return [
            'seo' => $this->seoService->page(['title' => $destination['name']]),
            'destination' => $destination,
            'featuredTours' => collect($this->tours())->where('destination_slug', $slug)->values()->all(),
            'categories' => $this->categories(),
            'blogPosts' => $this->blogPosts(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tourShow(string $locale, string $destinationSlug, string $tourSlug): array
    {
        $tour = collect($this->tours())->first(fn (array $item): bool => $item['destination_slug'] === $destinationSlug && $item['slug'] === $tourSlug);

        abort_if($tour === null || $tour['status'] !== 'published', 404);

        return [
            'seo' => $this->seoService->page([
                'title' => $tour['name'],
                'description' => $tour['short_description'],
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'TouristTrip',
                    'name' => $tour['name'],
                    'description' => $tour['short_description'],
                ],
            ]),
            'tour' => $tour,
            'options' => isset($tour['id']) ? $this->tourOptions((int) $tour['id']) : [],
            'relatedTours' => array_values(array_filter($this->tours(), fn (array $item): bool => $item['slug'] !== $tourSlug)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function destinations(): array
    {
        return Destination::query()
            ->active()
            ->with('translation')
            // One aggregate subquery rather than a count() per row: this list
            // renders on the home page and the destinations index.
            ->withCount(['tours' => fn ($query) => $query->where('status', 'published')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Destination $destination): array => [
                'slug' => (string) $destination->translation?->slug,
                'name' => (string) ($destination->translation?->name ?? $destination->code),
                'short_description' => (string) $destination->translation?->short_description,
                'image' => $destination->image_url,
                'tour_count' => (int) $destination->getAttribute('tours_count'),
                'active' => true,
            ])
            // A destination with no translation in any locale has no URL to
            // link to, so it cannot be rendered.
            ->filter(fn (array $destination): bool => $destination['slug'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tours(): array
    {
        $locale = app()->getLocale();

        return DB::table('tours')
            ->join('tour_translations', function ($join) use ($locale): void {
                $join->on('tour_translations.tour_id', '=', 'tours.id')
                    ->where('tour_translations.locale', $locale);
            })
            ->join('destinations', 'destinations.id', '=', 'tours.destination_id')
            ->join('destination_translations', function ($join) use ($locale): void {
                $join->on('destination_translations.destination_id', '=', 'destinations.id')
                    ->where('destination_translations.locale', $locale);
            })
            ->leftJoin('tour_categories', 'tour_categories.id', '=', 'tours.tour_category_id')
            ->leftJoin('tour_category_translations', function ($join) use ($locale): void {
                $join->on('tour_category_translations.tour_category_id', '=', 'tour_categories.id')
                    ->where('tour_category_translations.locale', $locale);
            })
            ->whereNull('tours.deleted_at')
            ->orderBy('tours.sort_order')
            ->select([
                'tours.id',
                'tours.code',
                'tours.image_url',
                'tours.status',
                'tours.duration_value',
                'tours.duration_unit',
                'tours.tour_type',
                'tours.is_featured',
                'tours.is_best_seller',
                'tours.is_last_minute',
                'tour_translations.name',
                'tour_translations.slug',
                'tour_translations.short_description',
                'tour_translations.description',
                'destination_translations.name as destination_name',
                'destination_translations.slug as destination_slug',
                'tour_category_translations.name as category_name',
                'destinations.image_url as destination_image',
            ])
            ->get()
            ->map(function (object $tour): array {
                return [
                    'id' => (int) $tour->id,
                    'slug' => (string) $tour->slug,
                    'destination_slug' => (string) $tour->destination_slug,
                    'destination' => (string) $tour->destination_name,
                    'category' => (string) ($tour->category_name ?: __('website.tours.title')),
                    'name' => (string) $tour->name,
                    'short_description' => (string) $tour->short_description,
                    'description' => (string) $tour->description,
                    'duration' => $this->durationLabel($tour->duration_value, $tour->duration_unit),
                    'languages' => $this->operatingLanguages((int) $tour->id),
                    'featured' => (bool) $tour->is_featured,
                    'best_seller' => (bool) $tour->is_best_seller,
                    'last_minute' => (bool) $tour->is_last_minute,
                    'status' => (string) $tour->status,
                    'type' => (string) $tour->tour_type,
                    'image' => $this->tourImage($tour->image_url, (string) $tour->destination_image),
                    'starting_price_label' => $this->startingPriceLabel((int) $tour->id),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function categories(): array
    {
        return TourCategory::query()
            ->active()
            ->with('translation')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TourCategory $category): array => [
                'slug' => (string) $category->translation?->slug,
                'name' => (string) ($category->translation?->name ?? $category->code),
                'description' => (string) $category->translation?->description,
            ])
            ->filter(fn (array $category): bool => $category['slug'] !== '')
            ->values()
            ->all();
    }

    /**
     * Published blog posts, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function blogPosts(int $limit = 3): array
    {
        return BlogPost::query()
            ->published()
            ->with('translation')
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (BlogPost $post): array => $this->mapPost($post))
            ->filter(fn (array $post): bool => $post['slug'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function blogIndex(string $locale): array
    {
        return [
            'seo' => $this->seoService->page([
                'title' => __('website.blog.title'),
                'description' => __('website.blog.meta_description'),
            ]),
            'posts' => $this->blogPosts(limit: 24),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function blogShow(string $locale, string $slug): array
    {
        $post = BlogPost::query()
            ->published()
            ->with('translation')
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('slug', $slug))
            ->first();

        abort_if($post === null, 404);

        $mapped = $this->mapPost($post);

        return [
            'seo' => $this->seoService->page([
                'title' => $post->translation?->seo_title ?: $mapped['title'],
                'description' => $post->translation?->seo_description ?: $mapped['excerpt'],
            ]),
            'post' => $mapped,
            'posts' => collect($this->blogPosts(limit: 4))
                ->reject(fn (array $other): bool => $other['slug'] === $mapped['slug'])
                ->take(3)
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPost(BlogPost $post): array
    {
        $translation = $post->translation;
        $slug = (string) $translation?->slug;

        return [
            'slug' => $slug,
            'title' => (string) ($translation?->title ?? $post->code),
            'excerpt' => (string) $translation?->excerpt,
            'body' => (string) $translation?->body,
            'image' => $post->image_url,
            'published_at' => $post->published_at,
            'url' => $slug === '' ? '#' : route('blog.show', ['locale' => app()->getLocale(), 'postSlug' => $slug]),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function testimonials(int $limit = 6): array
    {
        return Testimonial::query()
            ->active()
            ->with(['translation', 'tour.translation'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (Testimonial $testimonial): array => [
                'name' => $testimonial->author_name,
                'country' => $testimonial->author_country,
                'avatar' => $testimonial->avatar_url,
                'rating' => $testimonial->rating,
                'quote' => (string) $testimonial->translation?->quote,
                'tour' => $testimonial->tour?->translation?->name,
                'reviewed_on' => $testimonial->reviewed_on,
            ])
            ->filter(fn (array $testimonial): bool => $testimonial['quote'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $tables
     */
    private function canReadTables(array $tables): bool
    {
        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function durationLabel(mixed $value, mixed $unit): string
    {
        if ($value === null || $unit === null) {
            return __('website.booking_soon');
        }

        $unitLabel = $unit === 'hour' ? __('website.hours') : (string) $unit;

        return $value.' '.$unitLabel;
    }

    /**
     * @return list<string>
     */
    private function operatingLanguages(int $tourId): array
    {
        $languages = DB::table('tour_operating_languages')
            ->join('languages', 'languages.id', '=', 'tour_operating_languages.language_id')
            ->where('tour_operating_languages.tour_id', $tourId)
            ->where('languages.is_active', true)
            ->orderBy('languages.sort_order')
            ->pluck('languages.name')
            ->map(fn (mixed $name): string => (string) $name)
            ->values()
            ->all();

        return $languages === [] ? ['English'] : $languages;
    }

    private function startingPriceLabel(int $tourId): ?string
    {
        $price = DB::table('tour_prices')
            ->join('tour_options', 'tour_options.id', '=', 'tour_prices.tour_option_id')
            ->join('currencies', 'currencies.id', '=', 'tour_prices.currency_id')
            ->where('tour_options.tour_id', $tourId)
            ->where('tour_options.is_active', true)
            ->where('tour_prices.is_active', true)
            ->whereIn('tour_prices.guest_type', ['adult', 'private_group'])
            ->where('tour_prices.amount_minor', '>', 0)
            ->whereNull('tour_options.deleted_at')
            ->whereNull('tour_prices.deleted_at')
            ->tap(fn ($query) => $this->whereValidToday($query))
            ->orderBy('tour_prices.amount_minor')
            ->select(['tour_prices.amount_minor', 'currencies.symbol', 'currencies.code', 'currencies.decimal_places'])
            ->first();

        if ($price === null) {
            return null;
        }

        return $this->priceLabel(
            (int) $price->amount_minor,
            (string) $price->code,
            (string) $price->symbol,
            (int) $price->decimal_places,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tourOptions(int $tourId): array
    {
        if (! $this->canReadTables(['tour_options', 'tour_option_translations', 'tour_prices', 'currencies'])) {
            return [];
        }

        $locale = app()->getLocale();

        return DB::table('tour_options')
            ->join('tour_option_translations', function ($join) use ($locale): void {
                $join->on('tour_option_translations.tour_option_id', '=', 'tour_options.id')
                    ->where('tour_option_translations.locale', $locale);
            })
            ->where('tour_options.tour_id', $tourId)
            ->where('tour_options.is_active', true)
            ->whereNull('tour_options.deleted_at')
            ->orderByDesc('tour_options.is_default')
            ->orderBy('tour_options.sort_order')
            ->select([
                'tour_options.id',
                'tour_options.capacity',
                'tour_options.duration_value',
                'tour_options.duration_unit',
                'tour_options.is_private',
                'tour_options.is_default',
                'tour_option_translations.name',
                'tour_option_translations.short_description',
            ])
            ->get()
            ->map(fn (object $option): array => [
                'name' => (string) $option->name,
                'short_description' => (string) $option->short_description,
                'capacity' => (int) $option->capacity,
                'duration' => $this->durationLabel($option->duration_value, $option->duration_unit),
                'is_private' => (bool) $option->is_private,
                'is_default' => (bool) $option->is_default,
                'price_label' => $this->optionPriceLabel((int) $option->id),
            ])
            ->values()
            ->all();
    }

    private function optionPriceLabel(int $optionId): ?string
    {
        $price = DB::table('tour_prices')
            ->join('currencies', 'currencies.id', '=', 'tour_prices.currency_id')
            ->where('tour_prices.tour_option_id', $optionId)
            ->where('tour_prices.is_active', true)
            ->whereIn('tour_prices.guest_type', ['adult', 'private_group'])
            ->where('tour_prices.amount_minor', '>', 0)
            ->whereNull('tour_prices.deleted_at')
            ->tap(fn ($query) => $this->whereValidToday($query))
            ->orderBy('tour_prices.amount_minor')
            ->select(['tour_prices.amount_minor', 'currencies.symbol', 'currencies.code', 'currencies.decimal_places'])
            ->first();

        if ($price === null) {
            return null;
        }

        return $this->priceLabel(
            (int) $price->amount_minor,
            (string) $price->code,
            (string) $price->symbol,
            (int) $price->decimal_places,
        );
    }

    /**
     * Restrict a price query to rows in force today.
     *
     * `valid_from`/`valid_to` are both nullable and mean "no bound on that
     * side", so a row with neither always applies. Without this a seasonal
     * price stayed on the card forever once its window closed - which only
     * became reachable when the admin gained a way to set those dates.
     */
    private function whereValidToday(mixed $query): void
    {
        $today = now()->toDateString();

        $query->where(function ($outer) use ($today): void {
            $outer->whereNull('tour_prices.valid_from')
                ->orWhere('tour_prices.valid_from', '<=', $today);
        })->where(function ($outer) use ($today): void {
            $outer->whereNull('tour_prices.valid_to')
                ->orWhere('tour_prices.valid_to', '>=', $today);
        });
    }

    private function formatMinor(int $amountMinor, int $decimalPlaces, string $symbol, string $code): string
    {
        if ($decimalPlaces === 0) {
            return $symbol.$amountMinor.' '.$code;
        }

        $divisor = 10 ** $decimalPlaces;
        $major = intdiv($amountMinor, $divisor);
        $minor = $amountMinor % $divisor;

        return $symbol.$major.'.'.str_pad((string) $minor, $decimalPlaces, '0', STR_PAD_LEFT).' '.$code;
    }

    /**
     * A "from :price" label, converted into the visitor's selected currency.
     *
     * Both the tour-card starting price and the option price go through here, so
     * the conversion — and the fallback when a rate is missing — live in one
     * place rather than being duplicated per call site.
     */
    private function priceLabel(int $amountMinor, string $fromCode, string $symbol, int $decimalPlaces): string
    {
        $converted = app(CurrencyConverter::class)->convertMinor(
            $amountMinor,
            $fromCode,
            $this->settingsService->currentCurrency(),
        );

        if ($converted !== null) {
            return __('website.price_from', ['price' => $this->formatMinor(
                $converted['amount_minor'],
                $converted['decimal_places'],
                $converted['symbol'],
                $converted['code'],
            )]);
        }

        // No rate for this pair: show the original rather than nothing.
        return __('website.price_from', ['price' => $this->formatMinor($amountMinor, $decimalPlaces, $symbol, $fromCode)]);
    }

    /**
     * A tour's own image, then its destination's, then nothing.
     *
     * There was previously a hardcoded match on the tour code pointing at
     * specific photo IDs on an external CDN. That host reassigned one of those
     * IDs, so a photo of a gym appeared on a Luxor temple tour — and because
     * `tours` had no image column, no admin could correct it. Tours now carry
     * their own `image_url`; a blank one falls back to the destination's rather
     * than to a picture of something else entirely.
     */
    private function tourImage(?string $tourImage, string $destinationImage): ?string
    {
        return $tourImage ?: ($destinationImage ?: null);
    }
}

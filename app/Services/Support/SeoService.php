<?php

declare(strict_types=1);

namespace App\Services\Support;

final class SeoService
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function page(array $overrides = []): array
    {
        $locale = app()->getLocale();
        $canonical = url()->current();
        $title = $overrides['title'] ?? __('website.meta.default_title');

        return array_replace_recursive([
            'title' => $title,
            'description' => __('website.meta.default_description'),
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'image' => asset('favicon.ico'),
            'type' => 'website',
            'hreflang' => [
                ['locale' => 'en', 'url' => url('/en')],
                ['locale' => 'ar', 'url' => url('/ar')],
            ],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'TravelAgency',
                'name' => config('app.name', 'Hurgada guide'),
                'url' => $canonical,
                'inLanguage' => $locale,
            ],
        ], $overrides);
    }
}

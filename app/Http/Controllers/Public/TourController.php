<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Public\PublicPageService;
use App\Services\Support\SeoService;
use Illuminate\Contracts\View\View;

final class TourController
{
    public function __construct(
        private readonly PublicPageService $pageService,
        private readonly SeoService $seoService,
    ) {}

    public function index(string $locale, ?string $destinationSlug = null): View
    {
        return view('public.tours.index', [
            'seo' => $this->seoService->page(['title' => __('website.tours.title')]),
            'destinationSlug' => $destinationSlug,
        ]);
    }

    public function show(string $locale, string $destinationSlug, string $tourSlug): View
    {
        return view('public.tours.show', $this->pageService->tourShow($locale, $destinationSlug, $tourSlug));
    }
}

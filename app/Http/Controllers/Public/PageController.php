<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Support\SeoService;
use App\Services\Support\UiSettingsService;
use Illuminate\Contracts\View\View;

final class PageController
{
    public function __construct(
        private readonly SeoService $seoService,
        private readonly UiSettingsService $settingsService,
    ) {}

    public function about(): View
    {
        return view('public.pages.about', ['seo' => $this->seoService->page(['title' => __('website.nav.about')])]);
    }

    public function contact(): View
    {
        return view('public.contact.index', [
            'seo' => $this->seoService->page(['title' => __('website.nav.contact')]),
            'settings' => $this->settingsService->company(),
        ]);
    }

    public function faq(): View
    {
        return view('public.faq.index', ['seo' => $this->seoService->page(['title' => __('website.nav.faq')])]);
    }
}

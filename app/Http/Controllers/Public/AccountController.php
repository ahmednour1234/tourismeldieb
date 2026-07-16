<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Support\SeoService;
use Illuminate\Contracts\View\View;

final class AccountController
{
    public function __construct(
        private readonly SeoService $seoService,
    ) {}

    public function dashboard(): View
    {
        return view('public.account.dashboard', ['seo' => $this->seoService->page(['title' => __('website.account.dashboard')])]);
    }

    public function profile(): View
    {
        return view('public.account.profile', ['seo' => $this->seoService->page(['title' => __('website.account.profile')])]);
    }

    public function wishlist(): View
    {
        return view('public.account.wishlist', ['seo' => $this->seoService->page(['title' => __('website.account.wishlist')])]);
    }

    public function bookings(): View
    {
        return view('public.account.bookings', ['seo' => $this->seoService->page(['title' => __('website.account.bookings')])]);
    }
}

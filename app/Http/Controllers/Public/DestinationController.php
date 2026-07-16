<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Public\PublicPageService;
use Illuminate\Contracts\View\View;

final class DestinationController
{
    public function __construct(
        private readonly PublicPageService $pageService,
    ) {}

    public function index(string $locale): View
    {
        return view('public.destinations.index', $this->pageService->destinationIndex($locale));
    }

    public function show(string $locale, string $destinationSlug): View
    {
        return view('public.destinations.show', $this->pageService->destinationShow($locale, $destinationSlug));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Public\PublicPageService;
use Illuminate\Contracts\View\View;

final class HomeController
{
    public function __construct(
        private readonly PublicPageService $pageService,
    ) {}

    public function index(string $locale): View
    {
        return view('public.home.index', $this->pageService->home($locale));
    }
}

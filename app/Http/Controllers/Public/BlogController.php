<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Services\Public\PublicPageService;
use Illuminate\Contracts\View\View;

final class BlogController
{
    public function __construct(
        private readonly PublicPageService $pageService,
    ) {}

    public function index(string $locale): View
    {
        return view('public.blog.index', $this->pageService->blogIndex($locale));
    }

    public function show(string $locale, string $postSlug): View
    {
        return view('public.blog.show', $this->pageService->blogShow($locale, $postSlug));
    }
}

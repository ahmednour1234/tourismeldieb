<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale', config('app.locale', 'en'));

        abort_unless(in_array($locale, config('app.supported_locales', ['en', 'ar']), true), 404);

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}

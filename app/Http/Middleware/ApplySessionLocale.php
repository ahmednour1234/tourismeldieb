<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the locale remembered in the session to routes that live outside
 * the `{locale}` prefix group — login, password reset, and the whole admin
 * dashboard. Without this, those pages always render in the default locale
 * no matter which language the visitor chose.
 *
 * SetLocale still wins for `{locale}` routes: it runs later in the stack and
 * overwrites both the app locale and the session value from the URL segment.
 */
final class ApplySessionLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (is_string($locale) && in_array($locale, config('app.supported_locales', ['en', 'ar']), true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

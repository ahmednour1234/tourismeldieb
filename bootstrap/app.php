<?php

use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\ApplySessionLocale;
use App\Http\Middleware\SetLocale;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)
                ->by(Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip())));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.user' => ActiveUser::class,
            'set.locale' => SetLocale::class,
        ]);

        // Carries the visitor's chosen language onto routes that have no
        // {locale} prefix (login, password reset, /admin/*). SetLocale still
        // takes precedence on prefixed routes — it runs after this one.
        $middleware->web(append: ApplySessionLocale::class);

        // Already-authenticated visitors hitting guest-only pages (login,
        // password reset) belong on the dashboard. Without this, the guest
        // middleware falls back to '/' which resolves to the {locale} home
        // route and throws for a missing parameter.
        $middleware->redirectUsersTo(fn (): string => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

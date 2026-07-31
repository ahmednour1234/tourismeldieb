<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('auth.admin_login') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    {{-- A distinct, darker sign-in that reads as "control panel", not the
         customer-facing login. --}}
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        {{-- Ambient glow, purely decorative. --}}
        <div class="pointer-events-none absolute -top-40 -end-40 h-96 w-96 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -start-40 h-96 w-96 rounded-full bg-teal-700/10 blur-3xl"></div>

        <section class="relative w-full max-w-sm">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-500/15 ring-1 ring-teal-500/30">
                    <svg class="h-7 w-7 text-teal-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4z" />
                    </svg>
                </div>
                <h1 class="mt-5 text-2xl font-bold tracking-tight text-white">{{ __('auth.admin_login') }}</h1>
                <p class="mt-2 text-sm text-slate-400">{{ __('auth.admin_login_subtitle') }}</p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl backdrop-blur">
                @error('email')
                    <div class="mb-5 flex gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <form method="POST" action="{{ route('admin.login.authenticate') }}" class="grid gap-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300">{{ __('auth.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            autofocus
                            required
                            class="mt-1.5 w-full rounded-lg border-slate-700 bg-slate-950 text-white shadow-sm placeholder:text-slate-500 focus:border-teal-500 focus:ring-teal-500"
                        >
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between">
                            <label for="password" class="block text-sm font-medium text-slate-300">{{ __('auth.password_label') }}</label>
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-teal-400 hover:text-teal-300 hover:underline">
                                {{ __('auth.forgot_password') }}
                            </a>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="mt-1.5 w-full rounded-lg border-slate-700 bg-slate-950 text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input name="remember" value="1" type="checkbox" class="rounded border-slate-600 bg-slate-950 text-teal-500 focus:ring-teal-500 focus:ring-offset-slate-900">
                        {{ __('auth.remember') }}
                    </label>

                    <button
                        type="submit"
                        class="mt-2 w-full rounded-lg bg-teal-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-900/40 transition hover:bg-teal-400"
                    >
                        {{ __('auth.admin_login') }}
                    </button>
                </form>

                @if (app()->environment('local'))
                    <form method="POST" action="{{ route('admin.login.authenticate') }}" class="mt-5 border-t border-slate-800 pt-5">
                        @csrf
                        <input type="hidden" name="email" value="admin@hurgadaguide.example">
                        <input type="hidden" name="password" value="password">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('auth.demo_environment') }}</p>
                        <button type="submit" class="mt-2 w-full rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-800">
                            {{ __('auth.demo_login') }}
                        </button>
                    </form>
                @endif
            </div>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a href="{{ route('default-locale') }}" class="font-medium text-slate-400 hover:text-slate-200 hover:underline">
                    {{ __('auth.back_to_site') }}
                </a>
            </p>
        </section>
    </main>
</body>
</html>

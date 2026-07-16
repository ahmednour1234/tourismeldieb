<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? __('auth.login') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <main class="grid min-h-screen lg:grid-cols-2">
        {{-- Brand panel: decorative, hidden on small screens where it would
             push the form below the fold. --}}
        <aside class="relative hidden overflow-hidden bg-teal-800 lg:flex lg:flex-col lg:justify-between lg:p-12"
               aria-hidden="true">
            <div class="pointer-events-none absolute -top-24 -end-24 h-96 w-96 rounded-full bg-teal-700/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -start-16 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl"></div>

            <span class="relative text-2xl font-extrabold tracking-tight text-white">
                {{ __('website.brand') }}
            </span>

            <div class="relative">
                <p class="text-3xl font-bold leading-tight text-white">
                    {{ __('auth.brand_headline') }}
                </p>
                <p class="mt-4 max-w-md text-teal-100/90">
                    {{ __('auth.brand_subheadline') }}
                </p>
            </div>

            <p class="relative text-sm text-teal-200/70">
                &copy; {{ date('Y') }} {{ __('website.brand') }}
            </p>
        </aside>

        <div class="flex items-center justify-center px-4 py-10 sm:px-8">
            <section class="w-full max-w-sm">
                <a href="{{ route('default-locale') }}"
                   class="inline-block text-lg font-extrabold text-teal-800 lg:hidden">
                    {{ __('website.brand') }}
                </a>

                <div class="mt-6 lg:mt-0">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </main>
</body>
</html>

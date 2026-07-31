@php
    $settingsService = app(\App\Services\Support\UiSettingsService::class);
    $company = $settingsService->company();
    $languages = $settingsService->activeLanguages();
    $currentLocale = app()->getLocale();
    $direction = collect($languages)->firstWhere('code', $currentLocale)['direction'] ?? ($currentLocale === 'ar' ? 'rtl' : 'ltr');
    $seo = $seo ?? app(\App\Services\Support\SeoService::class)->page();
@endphp
<!DOCTYPE html>
{{-- `no-js` is removed by app.js the moment it runs. While present it forces
     [data-reveal] content visible, so a blocked bundle never leaves the page
     blank. --}}
<html lang="{{ $currentLocale }}" dir="{{ $direction }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo.meta :seo="$seo" />
    <x-seo.hreflang :items="$seo['hreflang'] ?? []" />
    <x-seo.json-ld :data="$seo['jsonLd'] ?? []" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <a href="#main-content" class="skip-link rounded-md bg-white px-4 py-2 text-sm font-semibold text-teal-800 shadow">{{ __('website.skip_to_content') }}</a>

    <header x-data="{ mobileOpen: false, destinationsOpen: false, languageOpen: false, accountOpen: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="{{ route('home', $currentLocale) }}" class="text-lg font-extrabold text-teal-800">{{ __('website.brand') }}</a>
            <nav class="hidden items-center gap-5 text-sm font-medium text-slate-700 lg:flex">
                <a href="{{ route('home', $currentLocale) }}" class="hover:text-teal-700">{{ __('website.nav.home') }}</a>
                <div class="relative">
                    <button type="button" x-on:click="destinationsOpen = !destinationsOpen" :aria-expanded="destinationsOpen.toString()" class="hover:text-teal-700">{{ __('website.nav.destinations') }}</button>
                    <div x-cloak x-show="destinationsOpen" x-on:click.outside="destinationsOpen = false" class="absolute start-0 mt-3 w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('destinations.index', $currentLocale) }}">{{ __('website.nav.destinations') }}</a>
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('tours.index', [$currentLocale, 'hurghada']) }}">{{ __('website.nav.hurghada') }}</a>
                        <a class="block rounded-md px-3 py-2 hover:bg-slate-50" href="{{ route('tours.index', [$currentLocale, 'el-gouna']) }}">{{ __('website.nav.el_gouna') }}</a>
                    </div>
                </div>
                <a href="{{ route('tours.all', $currentLocale) }}" class="hover:text-teal-700">{{ __('website.tours.title') }}</a>
                <a href="{{ route('pages.about', $currentLocale) }}" class="hover:text-teal-700">{{ __('website.nav.about') }}</a>
                <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="hover:text-teal-700">{{ __('website.nav.blog') }}</a>
                <a href="{{ route('contact', $currentLocale) }}" class="hover:text-teal-700">{{ __('website.nav.contact') }}</a>
            </nav>
            <div class="hidden items-center gap-3 lg:flex">
                <livewire:public.currency-switcher />
                <div class="relative">
                    <button type="button" x-on:click="languageOpen = !languageOpen" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold">{{ strtoupper($currentLocale) }}</button>
                    <div x-cloak x-show="languageOpen" x-on:click.outside="languageOpen = false" class="absolute end-0 mt-3 w-40 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                        @foreach ($languages as $language)
                            <a class="block rounded-md px-3 py-2 text-sm hover:bg-slate-50" href="{{ route('home', $language['code']) }}">{{ $language['flag'] }} {{ $language['native'] }}</a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('account.wishlist', $currentLocale) }}" class="text-sm font-semibold text-slate-700">{{ __('website.nav.wishlist') }}</a>
                <x-public.button :href="route('booking.create', ['locale' => $currentLocale])">{{ __('website.book_now') }}</x-public.button>
            </div>
            <button type="button" x-on:click="mobileOpen = !mobileOpen" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold lg:hidden" aria-controls="mobile-menu" :aria-expanded="mobileOpen.toString()">Menu</button>
        </div>
        <div id="mobile-menu" x-cloak x-show="mobileOpen" class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden">
            <div class="grid gap-3 text-sm font-medium">
                <a href="{{ route('home', $currentLocale) }}">{{ __('website.nav.home') }}</a>
                <a href="{{ route('destinations.index', $currentLocale) }}">{{ __('website.nav.destinations') }}</a>
                <a href="{{ route('tours.all', $currentLocale) }}">{{ __('website.tours.title') }}</a>
                <a href="{{ route('pages.about', $currentLocale) }}">{{ __('website.nav.about') }}</a>
                <a href="{{ route('contact', $currentLocale) }}">{{ __('website.nav.contact') }}</a>
                <x-public.whatsapp-button :number="$company['whatsapp']" />
            </div>
        </div>
    </header>

    <main id="main-content">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 md:grid-cols-4">
            <div>
                <p class="font-extrabold text-teal-800">{{ $company['name'] }}</p>
                <p class="mt-3 text-sm text-slate-600">{{ $company['description'] }}</p>
            </div>
            <div>
                <p class="font-semibold text-slate-950">{{ __('website.nav.destinations') }}</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-600">
                    <a href="{{ route('tours.index', [$currentLocale, 'hurghada']) }}">{{ __('website.nav.hurghada') }}</a>
                    <a href="{{ route('tours.index', [$currentLocale, 'el-gouna']) }}">{{ __('website.nav.el_gouna') }}</a>
                </div>
            </div>
            <div>
                <p class="font-semibold text-slate-950">{{ __('website.nav.contact') }}</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-600">
                    @if ($company['phone'])
                        <a href="tel:{{ preg_replace('/[^+\d]/', '', $company['phone']) }}" class="hover:text-teal-800">{{ $company['phone'] }}</a>
                    @endif
                    @if ($company['email'])
                        <a href="mailto:{{ $company['email'] }}" class="hover:text-teal-800">{{ $company['email'] }}</a>
                    @endif
                    @if ($company['address'])
                        <span>{{ $company['address'] }}</span>
                    @endif
                </div>

                {{-- Only the links an admin has actually set: the settings
                     screen leaves these blank by default rather than shipping
                     placeholder '#' icons that go nowhere. --}}
                @if ($company['social'] !== [])
                    <div class="mt-4 flex gap-3">
                        @foreach ($company['social'] as $network => $url)
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer me"
                                class="text-sm font-medium capitalize text-slate-600 underline-offset-4 hover:text-teal-800 hover:underline"
                            >{{ $network }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('newsletter.subscribe', ['locale' => $currentLocale]) }}" class="space-y-3">
                @csrf
                <p class="font-semibold text-slate-950">{{ __('website.newsletter') }}</p>
                @if (session('status'))
                    <p class="text-sm font-medium text-teal-700">{{ session('status') }}</p>
                @endif
                {{-- $errors is guarded because this footer renders on error
                     pages too (the 404 uses this layout), and those are drawn
                     outside the request that shares the validation bag. --}}
                @if (isset($errors) && $errors->has('email'))
                    <p class="text-sm text-red-600">{{ $errors->first('email') }}</p>
                @endif
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-md border-slate-300" placeholder="{{ __('website.email_placeholder') }}" aria-label="{{ __('website.newsletter') }}" required>
                <x-public.button type="submit">{{ __('website.subscribe') }}</x-public.button>
            </form>
        </div>
        <div class="border-t border-slate-200 px-4 py-4 text-center text-sm text-slate-500">&copy; {{ date('Y') }} {{ $company['name'] }}</div>
    </footer>

    {{-- "Live chat" and "Cookie preferences" lived here as `type="button"`
         elements wired to nothing at all: a visitor clicking either got
         silence. WhatsApp is the one channel that actually reaches you, so it
         is the one that stays. --}}
    <div class="fixed bottom-5 end-5 z-40 grid gap-2">
        @if ($company['whatsapp'])
            <x-public.whatsapp-button :number="$company['whatsapp']" />
        @endif
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>

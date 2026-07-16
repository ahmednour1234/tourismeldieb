<x-layouts.public :seo="$seo ?? []">
    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[260px_1fr]">
        <aside class="rounded-lg bg-white p-4 shadow-sm">
            <nav class="grid gap-2 text-sm font-semibold">
                <a href="{{ route('account.dashboard', app()->getLocale()) }}">{{ __('website.account.dashboard') }}</a>
                <a href="{{ route('account.profile', app()->getLocale()) }}">{{ __('website.account.profile') }}</a>
                <a href="{{ route('account.wishlist', app()->getLocale()) }}">{{ __('website.account.wishlist') }}</a>
                <a href="{{ route('account.bookings', app()->getLocale()) }}">{{ __('website.account.bookings') }}</a>
            </nav>
        </aside>
        <div>
            {{ $slot }}
        </div>
    </section>
</x-layouts.public>

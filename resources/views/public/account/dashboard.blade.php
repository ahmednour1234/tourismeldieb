<x-public.account.layout :seo="$seo">
    <x-public.section-heading :title="__('website.account.dashboard')" />

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('website.account.total_bookings') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-950 tabular-nums">{{ $bookingCount }}</p>
        </div>
        <a href="{{ route('booking.create', ['locale' => app()->getLocale()]) }}"
           class="rounded-lg border border-teal-200 bg-teal-50 p-5 transition hover:border-teal-400">
            <p class="text-sm font-semibold text-teal-800">{{ __('website.book_now') }}</p>
            <p class="mt-1 text-sm text-teal-700">{{ __('website.account.book_hint') }}</p>
        </a>
        <a href="{{ route('tours.all', ['locale' => app()->getLocale()]) }}"
           class="rounded-lg border border-slate-200 bg-white p-5 transition hover:border-slate-400">
            <p class="text-sm font-semibold text-slate-800">{{ __('website.tours.title') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('website.account.browse_hint') }}</p>
        </a>
    </div>

    <div class="mt-8">
        <div class="flex items-baseline justify-between">
            <h2 class="text-lg font-bold text-slate-950">{{ __('website.account.recent_bookings') }}</h2>
            @if ($bookingCount > 0)
                <a href="{{ route('account.bookings', ['locale' => app()->getLocale()]) }}" class="text-sm font-semibold text-teal-700 hover:underline">
                    {{ __('website.blog.view_all') }}
                </a>
            @endif
        </div>

        <div class="mt-4 grid gap-3">
            @forelse ($recentBookings as $booking)
                <x-public.booking-item :booking="$booking" />
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 p-10 text-center">
                    <p class="text-slate-500">{{ __('website.account.empty_bookings') }}</p>
                    <x-public.button class="mt-4" :href="route('booking.create', ['locale' => app()->getLocale()])">
                        {{ __('website.book_now') }}
                    </x-public.button>
                </div>
            @endforelse
        </div>
    </div>
</x-public.account.layout>

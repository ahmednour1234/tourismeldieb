<x-public.account.layout :seo="$seo">
    <x-public.section-heading :title="__('website.account.bookings')" />

    <div class="grid gap-3">
        @forelse ($bookings as $booking)
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
</x-public.account.layout>

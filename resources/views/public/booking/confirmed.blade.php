<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-2xl px-4 py-16">
        <div class="rounded-lg bg-white p-8 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-teal-50">
                <svg class="h-7 w-7 text-teal-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.8 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-bold text-slate-950">{{ __('website.booking.confirmed_title') }}</h1>
            <p class="mt-2 text-slate-600">{{ __('website.booking.confirmed_body', ['email' => $booking->customer_email]) }}</p>

            {{-- The reference is the one thing they need to keep. --}}
            <div class="mt-6 rounded-md border border-dashed border-slate-300 bg-slate-50 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('website.booking.reference') }}</p>
                <p class="mt-1 text-2xl font-bold tracking-widest text-slate-950">{{ $booking->reference }}</p>
            </div>

            <dl class="mt-6 grid gap-3 text-start text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">{{ __('website.booking.tour') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $booking->tour?->translation?->name ?? $booking->tour?->code }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">{{ __('website.booking.preferred_date') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $booking->preferred_date->isoFormat('D MMMM YYYY') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('website.booking.guests') }}</dt>
                    <dd class="font-medium text-slate-900">{{ $booking->total_guests }}</dd>
                </div>
            </dl>

            <p class="mt-6 text-sm text-slate-500">{{ __('website.booking.next_steps') }}</p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <x-public.button :href="route('tours.all', ['locale' => app()->getLocale()])">
                    {{ __('website.booking.browse_more') }}
                </x-public.button>
                <x-public.button :href="route('home', ['locale' => app()->getLocale()])" variant="secondary">
                    {{ __('website.nav.home') }}
                </x-public.button>
            </div>
        </div>
    </section>
</x-layouts.public>

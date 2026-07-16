@props(['tour'])
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    {{-- alt is empty: the heading below already names the tour, so announcing
         it twice is noise for a screen reader. --}}
    <x-public.image :src="$tour['image']" />
    <div class="space-y-4 p-5">
        <div class="flex flex-wrap gap-2">
            @if ($tour['featured'])
                <x-public.badge>{{ __('website.tours.featured') }}</x-public.badge>
            @endif
            @if ($tour['best_seller'])
                <x-public.badge tone="amber">{{ __('website.tours.best_seller') }}</x-public.badge>
            @endif
            @if ($tour['last_minute'])
                <x-public.badge tone="rose">{{ __('website.home.last_minute') }}</x-public.badge>
            @endif
        </div>
        <div>
            <p class="text-xs font-semibold uppercase text-teal-700">{{ $tour['destination'] }} / {{ $tour['category'] }}</p>
            <h3 class="mt-1 text-lg font-bold text-slate-950">{{ $tour['name'] }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ $tour['short_description'] }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-slate-700">{{ $tour['duration'] }}</span>
            <x-public.rating />
            @foreach ($tour['languages'] as $language)
                <x-public.language-badge :language="$language" />
            @endforeach
        </div>
        <p class="rounded-md bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">{{ $tour['starting_price_label'] ?? __('website.price_soon') }}</p>
        <div class="flex flex-wrap gap-2">
            <x-public.button :href="route('tours.show', [app()->getLocale(), $tour['destination_slug'], $tour['slug']])">{{ __('website.view_details') }}</x-public.button>
            <x-public.button
                :href="route('booking.create', ['locale' => app()->getLocale(), 'tour' => $tour['slug']])"
                variant="secondary"
            >{{ __('website.book_now') }}</x-public.button>
        </div>
    </div>
</article>

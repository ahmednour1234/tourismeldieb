@props(['tour'])
<article data-reveal class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-xl hover:shadow-slate-200/60">
    {{-- alt is empty: the heading below already names the tour, so announcing
         it twice is noise for a screen reader. The image sits in a clipped
         frame so it can scale on hover without spilling. --}}
    <div class="overflow-hidden">
        <x-public.image :src="$tour['image']" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105" />
    </div>
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
            @if ($tour['duration'] !== '')
                <span class="text-sm text-slate-700">{{ $tour['duration'] }}</span>
            @endif
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

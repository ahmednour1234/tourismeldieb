@props(['destination'])
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <x-public.image :src="$destination['image']" />
    <div class="p-5">
        <h3 class="text-lg font-bold text-slate-950">{{ $destination['name'] }}</h3>
        <p class="mt-2 text-sm text-slate-600">{{ $destination['short_description'] }}</p>
        <p class="mt-3 text-xs font-semibold uppercase text-teal-700">{{ $destination['tour_count'] }} {{ __('website.destinations.published_tours') }}</p>
        <x-public.button class="mt-4" :href="route('destinations.show', [app()->getLocale(), $destination['slug']])" variant="secondary">{{ __('website.destinations.view_tours') }}</x-public.button>
    </div>
</article>

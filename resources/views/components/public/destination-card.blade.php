@props(['destination'])
<article data-reveal class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-xl hover:shadow-slate-200/60">
    <div class="overflow-hidden">
        <x-public.image :src="$destination['image']" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105" />
    </div>
    <div class="p-5">
        <h3 class="text-lg font-bold text-slate-950">{{ $destination['name'] }}</h3>
        <p class="mt-2 text-sm text-slate-600">{{ $destination['short_description'] }}</p>
        <p class="mt-3 text-xs font-semibold uppercase text-teal-700">{{ $destination['tour_count'] }} {{ __('website.destinations.published_tours') }}</p>
        <x-public.button class="mt-4" :href="route('destinations.show', [app()->getLocale(), $destination['slug']])" variant="secondary">{{ __('website.destinations.view_tours') }}</x-public.button>
    </div>
</article>

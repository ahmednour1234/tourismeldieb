<x-layouts.public :seo="$seo">
    <section class="relative overflow-hidden bg-slate-950 text-white">
        <img class="absolute inset-0 h-full w-full object-cover opacity-55" src="{{ $destination['image'] }}" alt="{{ $destination['name'] }}">
        <div class="relative mx-auto max-w-7xl px-4 py-24">
            <x-public.breadcrumb :items="[['label' => __('website.nav.home'), 'url' => route('home', app()->getLocale())], ['label' => __('website.destinations.title'), 'url' => route('destinations.index', app()->getLocale())], ['label' => $destination['name']]]" />
            <h1 class="mt-6 text-4xl font-black sm:text-6xl">{{ $destination['name'] }}</h1>
            <p class="mt-4 max-w-2xl text-lg text-slate-100">{{ $destination['short_description'] }}</p>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.section-heading :title="__('website.home.featured_tours')" />
        <div class="grid gap-5 lg:grid-cols-3">
            @forelse ($featuredTours as $tour)
                <x-public.tour-card :tour="$tour" />
            @empty
                <x-public.empty-state :title="__('website.tours.empty')" />
            @endforelse
        </div>
    </section>
    <section class="bg-white py-12">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold">{{ __('website.destinations.seo_content') }}</h2>
                <p class="mt-3 text-slate-600">{{ $destination['short_description'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-6">
                <h3 class="font-bold">{{ __('website.destinations.map') }}</h3>
                <div class="mt-3 aspect-video rounded-md bg-slate-100"></div>
            </div>
        </div>
    </section>
</x-layouts.public>

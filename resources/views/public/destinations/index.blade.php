<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.breadcrumb :items="[['label' => __('website.nav.home'), 'url' => route('home', app()->getLocale())], ['label' => __('website.destinations.title')]]" />
        <x-public.section-heading class="mt-6" :title="__('website.destinations.title')" :copy="__('website.home.meta_description')" />
        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($destinations as $destination)
                <x-public.destination-card :destination="$destination" />
            @endforeach
        </div>
    </section>
</x-layouts.public>

<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.breadcrumb :items="[['label' => __('website.nav.home'), 'url' => route('home', app()->getLocale())], ['label' => __('website.tours.title')]]" />
        <x-public.section-heading class="mt-6" :title="__('website.tours.title')" :copy="__('website.home.meta_description')" />
        <livewire:public.tours.tour-search :destination-slug="$destinationSlug" />
    </section>
</x-layouts.public>

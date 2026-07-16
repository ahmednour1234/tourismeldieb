<x-layouts.public>
    <section class="mx-auto max-w-3xl px-4 py-20 text-center">
        <h1 class="text-5xl font-black">404</h1>
        <p class="mt-4 text-slate-600">{{ __('website.errors.404') }}</p>
        <x-public.button class="mt-8" :href="route('default-locale')">{{ __('website.errors.home') }}</x-public.button>
    </section>
</x-layouts.public>

<x-layouts.auth :title="__('auth.verify_email')">
    <h1 class="text-2xl font-bold text-slate-950">{{ __('auth.verify_email') }}</h1>
    <p class="mt-4 text-slate-600">{{ __('auth.verify_email') }}</p>
    <x-public.button class="mt-6" :href="route('default-locale')">{{ __('website.errors.home') }}</x-public.button>
</x-layouts.auth>

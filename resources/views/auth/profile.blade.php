<x-layouts.auth :title="__('auth.profile')">
    <h1 class="text-2xl font-bold text-slate-950">{{ __('auth.profile') }}</h1>
    <form class="mt-6 grid gap-4">
        <x-forms.input name="name" :label="__('website.forms.name')" />
        <x-forms.input name="email" type="email" :label="__('auth.email')" />
        <x-public.button type="button">{{ __('admin.actions.save') }}</x-public.button>
    </form>
</x-layouts.auth>

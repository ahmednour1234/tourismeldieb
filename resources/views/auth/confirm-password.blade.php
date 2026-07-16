<x-layouts.auth :title="__('auth.confirm_password')">
    <h1 class="text-2xl font-bold text-slate-950">{{ __('auth.confirm_password') }}</h1>
    <form class="mt-6 grid gap-4">
        <x-forms.input name="password" type="password" :label="__('auth.password_label')" required />
        <x-public.button type="button">{{ __('auth.submit') }}</x-public.button>
    </form>
</x-layouts.auth>

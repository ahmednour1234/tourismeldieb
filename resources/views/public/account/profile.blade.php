<x-public.account.layout :seo="$seo">
    <x-public.section-heading :title="__('website.account.profile')" />
    <form class="rounded-lg bg-white p-6 shadow-sm">
        <div class="grid gap-4">
            <x-forms.input name="name" :label="__('website.forms.name')" />
            <x-forms.input name="email" type="email" :label="__('website.forms.email')" />
            <x-public.button type="button">{{ __('admin.actions.save') }}</x-public.button>
        </div>
    </form>
</x-public.account.layout>

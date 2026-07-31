<x-public.account.layout :seo="$seo">
    <x-public.section-heading :title="__('website.account.profile')" />

    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <ul class="list-inside list-disc">
                @foreach ($errors->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Previously a dead form: no method, no action, and type="button" on
         submit — editing your name did nothing. --}}
    <form method="POST" action="{{ route('account.profile.update', ['locale' => app()->getLocale()]) }}" class="rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid gap-4">
            <x-forms.input
                name="name"
                :label="__('website.forms.name')"
                :value="old('name', $user->name)"
                autocomplete="name"
                required
            />
            <x-forms.input
                name="email"
                type="email"
                :label="__('website.forms.email')"
                :value="old('email', $user->email)"
                autocomplete="email"
                required
            />
        </div>

        <div class="mt-6 border-t border-slate-200 pt-6">
            <p class="text-sm font-semibold text-slate-900">{{ __('website.account.change_password') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('website.account.change_password_hint') }}</p>

            <div class="mt-4 grid gap-4">
                <x-forms.input
                    name="current_password"
                    type="password"
                    :label="__('website.account.current_password')"
                    autocomplete="current-password"
                />
                <x-forms.input
                    name="password"
                    type="password"
                    :label="__('website.account.new_password')"
                    autocomplete="new-password"
                />
                <x-forms.input
                    name="password_confirmation"
                    type="password"
                    :label="__('auth.confirm_password')"
                    autocomplete="new-password"
                />
            </div>
        </div>

        <x-public.button type="submit" class="mt-6">{{ __('admin.actions.save') }}</x-public.button>
    </form>
</x-public.account.layout>

<x-layouts.auth :title="__('auth.register')">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('auth.register') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.register_subtitle') }}</p>
    </header>

    @error('email')
        <div class="mt-6 flex gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('register.store') }}">
        @csrf

        <x-forms.input
            name="name"
            :label="__('auth.name')"
            :value="old('name')"
            autocomplete="name"
            autofocus
            required
        />

        <x-forms.input
            name="email"
            type="email"
            :label="__('auth.email')"
            :value="old('email')"
            autocomplete="username"
            required
        />

        <x-forms.input
            name="password"
            type="password"
            :label="__('auth.password_label')"
            autocomplete="new-password"
            required
        />

        <x-forms.input
            name="password_confirmation"
            type="password"
            :label="__('auth.confirm_password')"
            autocomplete="new-password"
            required
        />

        <x-public.button type="submit" class="w-full justify-center">{{ __('auth.register') }}</x-public.button>
    </form>

    <p class="mt-6 text-sm text-slate-500">
        {{ __('auth.have_account') }}
        <a class="font-semibold text-teal-700 hover:text-teal-900 hover:underline" href="{{ route('login') }}">
            {{ __('auth.login') }}
        </a>
    </p>
</x-layouts.auth>

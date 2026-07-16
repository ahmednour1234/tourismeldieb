<x-layouts.auth :title="__('auth.reset_password')">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('auth.reset_password') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.reset_password_subtitle') }}</p>
    </header>

    @error('email')
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            {{ $message }}
        </div>
    @enderror

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-forms.input
            name="email"
            type="email"
            :label="__('auth.email')"
            :value="old('email', $email)"
            autocomplete="username"
            required
        />

        <x-forms.input
            name="password"
            type="password"
            :label="__('auth.new_password')"
            autocomplete="new-password"
            autofocus
            required
        />

        <x-forms.input
            name="password_confirmation"
            type="password"
            :label="__('auth.confirm_new_password')"
            autocomplete="new-password"
            required
        />

        <x-public.button type="submit" class="w-full justify-center">{{ __('auth.reset_password') }}</x-public.button>
    </form>

    <a class="mt-6 inline-block text-sm font-semibold text-teal-700 hover:text-teal-900 hover:underline"
       href="{{ route('login') }}">{{ __('auth.back_to_login') }}</a>
</x-layouts.auth>

<x-layouts.auth :title="__('auth.forgot_password')">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('auth.forgot_password') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.forgot_password_subtitle') }}</p>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-900"
             role="status">
            {{ session('status') }}
        </div>
    @endif

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-forms.input
            name="email"
            type="email"
            :label="__('auth.email')"
            autocomplete="username"
            autofocus
            required
        />
        <x-public.button type="submit" class="w-full justify-center">{{ __('auth.send_reset_link') }}</x-public.button>
    </form>

    <a class="mt-6 inline-block text-sm font-semibold text-teal-700 hover:text-teal-900 hover:underline"
       href="{{ route('login') }}">{{ __('auth.back_to_login') }}</a>
</x-layouts.auth>

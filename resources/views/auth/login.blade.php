<x-layouts.auth :title="__('auth.login')">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('auth.login') }}</h1>
        <p class="mt-2 text-sm text-slate-500">{{ __('auth.login_subtitle') }}</p>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-900"
             role="status">
            {{ session('status') }}
        </div>
    @endif

    @error('email')
        <div class="mt-6 flex gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
             role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('login.authenticate') }}">
        @csrf

        <x-forms.input
            name="email"
            type="email"
            :label="__('auth.email')"
            autocomplete="username"
            autofocus
            required
        />

        <div>
            <div class="flex items-baseline justify-between">
                <x-forms.label for="password" :required="true">{{ __('auth.password_label') }}</x-forms.label>
                <a class="text-xs font-semibold text-teal-700 hover:text-teal-900 hover:underline"
                   href="{{ route('password.request') }}">{{ __('auth.forgot_password') }}</a>
            </div>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700"
            >
            <x-forms.error name="password" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input name="remember" value="1" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
            {{ __('auth.remember') }}
        </label>

        <x-public.button type="submit" class="w-full justify-center">{{ __('auth.login') }}</x-public.button>
    </form>

    <p class="mt-6 text-sm text-slate-500">
        {{ __('auth.no_account') }}
        <a class="font-semibold text-teal-700 hover:text-teal-900 hover:underline" href="{{ route('register') }}">
            {{ __('auth.register') }}
        </a>
    </p>

    @if (app()->environment('local'))
        {{-- Local-only convenience. The demo password is posted from a hidden
             field rather than pre-filled into the visible password input, so
             it is never captured by the browser's password manager. --}}
        <form class="mt-8 rounded-md border border-dashed border-slate-300 bg-slate-50 p-4"
              method="POST" action="{{ route('login.authenticate') }}">
            @csrf
            <input type="hidden" name="email" value="admin@hurgadaguide.example">
            <input type="hidden" name="password" value="password">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('auth.demo_environment') }}</p>
            <button type="submit"
                    class="mt-2 inline-flex w-full items-center justify-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                {{ __('auth.demo_login') }}
            </button>
        </form>
    @endif
</x-layouts.auth>

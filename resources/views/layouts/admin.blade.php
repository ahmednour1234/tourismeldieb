<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('admin.dashboard.title') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @livewireStyles
</head>
<body x-data="adminShell" x-init="init" class="bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen lg:flex">
        <aside class="fixed inset-y-0 start-0 z-40 w-72 border-e border-slate-200 bg-white p-4 transition lg:static dark:border-slate-800 dark:bg-slate-900" x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="font-extrabold text-teal-800">{{ __('website.brand') }}</a>
                <button type="button" class="lg:hidden" x-on:click="sidebarOpen = false">Close</button>
            </div>
            <nav class="mt-6 space-y-6">
                <div>
                    <p class="px-3 text-xs font-bold uppercase text-slate-400">{{ __('admin.nav.dashboard') }}</p>
                    <x-admin.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('admin.nav.dashboard') }}</x-admin.sidebar-link>
                </div>
                @foreach ([
                    __('admin.nav.bookings') => ['bookings'],
                    __('admin.nav.catalog') => ['countries', 'destinations', 'categories', 'tours'],
                    __('admin.nav.content') => ['posts', 'testimonials'],
                    __('admin.nav.localization') => ['languages', 'currencies'],
                    __('admin.nav.users_access') => ['users', 'roles'],
                    __('admin.nav.configuration') => ['settings'],
                ] as $section => $links)
                    <div>
                        <p class="px-3 text-xs font-bold uppercase text-slate-400">{{ $section }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($links as $link)
                                @can($link.'.view')
                                    <x-admin.sidebar-link :href="route('admin.'.$link.'.index')" :active="request()->routeIs('admin.'.$link.'.*')">{{ __('admin.resources.'.$link) }}</x-admin.sidebar-link>
                                @endcan
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div class="flex items-center justify-between">
                    <button type="button" x-on:click="sidebarOpen = true" class="rounded-md border border-slate-200 px-3 py-2 lg:hidden">{{ __('admin.nav.catalog') }}</button>
                    <input type="search" class="hidden w-96 rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-950 md:block" placeholder="{{ __('admin.actions.search') }}">
                    <div class="flex items-center gap-3">
                        <button type="button" x-on:click="toggleDarkMode" class="rounded-md border border-slate-200 px-3 py-2 text-sm dark:border-slate-700">{{ __('admin.actions.dark_mode') }}</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('admin.actions.logout') }}</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="p-4 sm:p-6">
                <x-admin.flash-message />
                <div class="mt-4">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>

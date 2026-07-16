@props(['title', 'action' => null])
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm text-slate-500">{{ __('admin.nav.dashboard') }}</p>
        <h1 class="text-2xl font-bold text-slate-950 dark:text-white">{{ $title }}</h1>
    </div>
    @if ($action)
        <x-public.button :href="$action['href']">{{ $action['label'] }}</x-public.button>
    @endif
</div>

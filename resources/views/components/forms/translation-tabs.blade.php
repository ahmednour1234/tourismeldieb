@props(['languages'])
<div x-data="{ tab: '{{ $languages[0]['code'] ?? 'en' }}' }" class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        @foreach ($languages as $language)
            <button type="button" x-on:click="tab = '{{ $language['code'] }}'" x-bind:class="tab === '{{ $language['code'] }}' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200'" class="rounded-md px-3 py-2 text-sm font-semibold">
                {{ $language['flag'] }} {{ $language['native'] }}
            </button>
        @endforeach
    </div>
    <div class="pt-4">
        {{ $slot }}
    </div>
</div>

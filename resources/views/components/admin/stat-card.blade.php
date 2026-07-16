@props(['label', 'value', 'href' => null, 'hint' => null])

{{-- A stat tile is a link when the resource it counts has an index page: the
     number invites a follow-up ("14 tours — which ones?") and a dead tile
     forces a trip to the sidebar. --}}
@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'block rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition dark:border-slate-800 dark:bg-slate-900'.($href ? ' hover:border-teal-600 hover:shadow-md dark:hover:border-teal-500' : '')]) }}
>
    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold text-slate-950 tabular-nums dark:text-white">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>
    @endif
</{{ $tag }}>

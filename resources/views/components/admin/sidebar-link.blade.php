@props(['href', 'active' => false])
<a href="{{ $href }}" {{ $attributes->merge(['class' => $active ? 'block rounded-md bg-teal-700 px-3 py-2 text-sm font-semibold text-white' : 'block rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800']) }}>
    {{ $slot }}
</a>

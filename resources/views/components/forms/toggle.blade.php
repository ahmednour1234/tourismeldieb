@props(['name', 'label', 'value' => false, 'help' => null])

{{-- The hidden "0" is what makes a toggle switchable *off*: an unchecked
     checkbox posts nothing at all, so without it the request would carry no
     key and the old value would survive every save. --}}
<div>
    <label class="flex items-center gap-3 text-sm font-medium text-slate-700 dark:text-slate-200">
        <input type="hidden" name="{{ $name }}" value="0">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="1"
            @checked((bool) old($name, $value))
            {{ $attributes->merge(['class' => 'rounded border-slate-300 text-teal-700 focus:ring-teal-700 dark:border-slate-600 dark:bg-slate-950']) }}
        >
        <span>{{ $label }}</span>
    </label>
    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif
    <x-forms.error :name="$name" />
</div>

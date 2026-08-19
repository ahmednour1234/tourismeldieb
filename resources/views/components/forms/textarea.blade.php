@props(['name', 'label', 'value' => null, 'required' => false, 'help' => null])
{{-- `old()` can hand back an array when a list field fails validation, and
     rendering an array into a textarea is a fatal error. Lists are joined back
     into one-per-line text, which is exactly how they were typed. --}}
@php
    $current = old($name, $value);
    $current = is_array($current) ? implode("\n", array_filter($current, 'is_scalar')) : $current;
@endphp
<div>
    <x-forms.label :for="$name" :required="$required">{{ $label }}</x-forms.label>
    <textarea id="{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->merge(['class' => 'mt-1 min-h-32 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white']) }}>{{ $current }}</textarea>
    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif
    <x-forms.error :name="$name" />
</div>

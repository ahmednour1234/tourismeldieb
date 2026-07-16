@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false, 'help' => null])

{{-- Selection is compared loosely on purpose. PHP casts numeric array keys to
     int, so an id-keyed option list ("3" from the request vs 3 from the key)
     never matches under ===, and the saved relation would silently render as
     unselected on every edit. --}}
@php($current = old($name, $value))
<div>
    <x-forms.label :for="$name" :required="$required">{{ $label }}</x-forms.label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @required($required)
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white']) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current !== null && $current !== '' && (string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif
    <x-forms.error :name="$name" />
</div>

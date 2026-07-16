@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null])
{{-- Password fields never repopulate: `old()` would echo the submitted
     secret back into the rendered HTML after a failed validation pass. --}}
@php($isPassword = $type === 'password')
<div>
    <x-forms.label :for="$name" :required="$required">{{ $label }}</x-forms.label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @unless($isPassword) value="{{ old($name, $value) }}" @endunless @required($required) @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white']) }}>
    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif
    <x-forms.error :name="$name" />
</div>

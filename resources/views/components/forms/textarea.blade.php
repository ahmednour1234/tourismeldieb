@props(['name', 'label', 'value' => null, 'required' => false])
<div>
    <x-forms.label :for="$name" :required="$required">{{ $label }}</x-forms.label>
    <textarea id="{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->merge(['class' => 'mt-1 min-h-32 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white']) }}>{{ old($name, $value) }}</textarea>
    <x-forms.error :name="$name" />
</div>

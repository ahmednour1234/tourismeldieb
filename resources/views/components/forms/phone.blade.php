@props([
    'name',
    'label',
    'codeName' => null,
    'value' => null,
    'required' => false,
    'help' => null,
])

{{--
    A phone input paired with a dialling-code select.

    The two controls post separately but are stored as one string, so on
    redisplay the stored value has to be taken apart again — see
    App\Support\PhoneNumber. `old()` wins over that split, otherwise a failed
    validation pass would discard whichever half the customer had just fixed.

    The label is bound to the number input rather than the select: clicking it
    should put the cursor where a person actually types.
--}}
@php
    $codeField = $codeName ?? $name.'_code';
    $parts = \App\Support\PhoneNumber::split($value);
    $currentCode = old($codeField, $parts['code'] ?? \App\Support\DiallingCodes::DEFAULT);
    $currentLocal = old($name, $parts['local']);
    // Either half being rejected should light up both, since the customer sees
    // one field and cannot tell which control the message belongs to.
    $hasError = $errors->has($name) || $errors->has($codeField);
@endphp

<div>
    <x-forms.label :for="$name" :required="$required">{{ $label }}</x-forms.label>

    <div class="mt-1 flex gap-2">
        <select
            id="{{ $codeField }}"
            name="{{ $codeField }}"
            aria-label="{{ __('website.forms.dialling_code') }}"
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            class="w-40 shrink-0 rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        >
            @foreach (\App\Support\DiallingCodes::options() as $code => $optionLabel)
                <option value="{{ $code }}" @selected((string) $currentCode === (string) $code)>{{ $optionLabel }}</option>
            @endforeach
        </select>

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="tel"
            inputmode="tel"
            autocomplete="tel-national"
            {{-- A phone number reads left-to-right in every locale. Without
                 this the Arabic page renders "+20 1000" with the plus and the
                 groups reordered, and the customer corrects a number that was
                 never wrong. --}}
            dir="ltr"
            value="{{ $currentLocal }}"
            @required($required)
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->merge(['class' => 'w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700 dark:border-slate-700 dark:bg-slate-950 dark:text-white']) }}
        >
    </div>

    @if ($help)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
    @endif

    <x-forms.error :name="$codeField" />
    <x-forms.error :name="$name" />
</div>

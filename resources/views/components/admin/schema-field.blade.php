@props(['name', 'field', 'value' => null, 'options' => [], 'labelOverride' => null])

{{--
    Renders one field from a ResourceSchema definition.

    The admin previously hardcoded name/code/status/active for all nine
    resources, matching almost none of the real tables. Driving the control
    from the schema keeps the form, the validation rules and the columns in
    agreement.
--}}
@php
    // labelOverride carries a value that is already display text (a language's
    // native name on the per-locale settings inputs), so it must not go
    // through __() — "العربية" is not a translation key.
    $label = $labelOverride ?? __($field['label']);
    $help = isset($field['help']) ? __($field['help']) : null;
    $required = in_array('required', $field['rules'] ?? [], true);
    $default = $field['default'] ?? null;
    $current = $value ?? $default;
@endphp

@switch($field['type'])
    @case('toggle')
        <x-forms.toggle
            :name="$name"
            :label="$label"
            :value="(bool) $current"
            :help="$help"
        />
        @break

    @case('select')
        <x-forms.select
            :name="$name"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$current"
            :options="collect($field['options'] ?? [])->map(fn (string $option): string => __($option))->all()"
        />
        @break

    @case('relation')
        <x-forms.select
            :name="$name"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$current"
            :options="$options"
            :placeholder="$required ? null : __('admin.fields.none')"
        />
        @break

    @case('textarea')
        <x-forms.textarea
            :name="$name"
            :label="$label"
            :required="$required"
            :value="$current"
        />
        @break

    @case('number')
        <x-forms.input
            :name="$name"
            type="number"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$current"
        />
        @break

    @case('money')
        {{-- Entered in major units (19.50) and stored in minor ones (1950);
             App\Admin\Money converts in both directions. `step` allows two
             decimals rather than the whole numbers a bare number input would
             enforce, which would make every price a round dollar. --}}
        <x-forms.input
            :name="$name"
            type="number"
            step="0.01"
            min="0"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$current"
        />
        @break

    @case('date')
    @case('datetime')
        {{-- Carbon casts render as "2026-07-16 21:30:00", which a native date
             input silently rejects and shows as blank. Format to what the
             control actually accepts. --}}
        @php
            $format = $field['type'] === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i';
            $dateValue = $current instanceof \DateTimeInterface
                ? $current->format($format)
                : ($current ? \Illuminate\Support\Carbon::parse($current)->format($format) : null);
        @endphp
        <x-forms.input
            :name="$name"
            :type="$field['type'] === 'date' ? 'date' : 'datetime-local'"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$dateValue"
        />
        @break

    @case('password')
        <x-forms.input
            :name="$name"
            type="password"
            :label="$label"
            :required="$required"
            :help="$help"
            autocomplete="new-password"
        />
        @break

    @default
        <x-forms.input
            :name="$name"
            :label="$label"
            :required="$required"
            :help="$help"
            :value="$current"
        />
@endswitch

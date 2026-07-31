@props(['href' => null, 'variant' => 'primary'])

{{--
    Button variants:
    - primary   : teal fill, white text — the default call to action
    - secondary : teal outline — for a light background
    - inverse   : white fill, teal text — for a DARK/teal background, where
                  `secondary` would render teal-on-teal and vanish. This was the
                  invisible button in the "Need help choosing?" box.
--}}
@php($classes = match ($variant) {
    'secondary' => 'border border-teal-600 bg-teal-50 text-teal-800 hover:bg-teal-100 hover:border-teal-700',
    'inverse' => 'bg-white text-teal-800 hover:bg-teal-50',
    default => 'bg-teal-700 text-white hover:bg-teal-800',
})
@if ($href)
    <a {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition {$classes}", 'href' => $href]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition {$classes}"]) }}>{{ $slot }}</button>
@endif

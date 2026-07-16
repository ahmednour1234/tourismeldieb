@props(['href' => null, 'variant' => 'primary'])
@php($classes = $variant === 'secondary' ? 'border border-teal-700 text-teal-800 hover:bg-teal-50' : 'bg-teal-700 text-white hover:bg-teal-800')
@if ($href)
    <a {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition {$classes}", 'href' => $href]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition {$classes}"]) }}>{{ $slot }}</button>
@endif

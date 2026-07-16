@props(['tone' => 'teal'])
@php($classes = ['teal' => 'bg-teal-50 text-teal-800', 'amber' => 'bg-amber-50 text-amber-800', 'rose' => 'bg-rose-50 text-rose-800'][$tone] ?? 'bg-slate-100 text-slate-700')
<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>{{ $slot }}</span>

@props(['title', 'copy' => null])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center']) }}>
    <p class="font-semibold text-slate-900">{{ $title }}</p>
    @if ($copy)
        <p class="mt-2 text-sm text-slate-600">{{ $copy }}</p>
    @endif
</div>

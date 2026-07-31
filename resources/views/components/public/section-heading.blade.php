@props(['title', 'copy' => null])
<div data-reveal class="mb-6 max-w-3xl">
    <h2 class="text-2xl font-bold tracking-normal text-slate-950 sm:text-3xl">{{ $title }}</h2>
    @if ($copy)
        <p class="mt-2 text-slate-600">{{ $copy }}</p>
    @endif
</div>

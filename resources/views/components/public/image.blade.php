@props(['src' => null, 'alt' => '', 'class' => 'h-52 w-full object-cover'])

{{--
    An image, or a neutral placeholder when there is none.

    Images are admin-set per row and may legitimately be blank. They used to be
    hotlinked stock photos hardcoded by slug — until the host reassigned one of
    the IDs and a gym photo appeared on a Luxor temple tour. A missing image is
    honest; a confidently wrong one is not.
--}}
@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" {{ $attributes->merge(['class' => $class]) }}>
@else
    <div {{ $attributes->merge(['class' => $class.' flex items-center justify-center bg-slate-100']) }} aria-hidden="true">
        <svg class="h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Zm2 12.5V19h12v-3.5l-3.2-3.2a1 1 0 0 0-1.4 0L10 15.7l-1.4-1.4a1 1 0 0 0-1.4 0L6 17.5ZM9 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
        </svg>
    </div>
@endif

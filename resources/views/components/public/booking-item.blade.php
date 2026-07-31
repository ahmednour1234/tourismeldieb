@props(['booking'])

@php
    $palette = match ($booking->status) {
        'confirmed' => 'border-teal-200 bg-teal-50 text-teal-800',
        'completed' => 'border-slate-200 bg-slate-50 text-slate-700',
        'cancelled' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-amber-200 bg-amber-50 text-amber-800',
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4">
    <div class="min-w-0">
        <p class="font-semibold text-slate-900">
            {{ $booking->tour?->translation?->name ?? $booking->tour?->code ?? '—' }}
        </p>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $booking->preferred_date->isoFormat('D MMMM YYYY') }}
            · {{ __('website.booking.guests') }}: {{ $booking->total_guests }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            {{ __('website.account.reference') }}: <span class="font-medium tracking-wide">{{ $booking->reference }}</span>
        </p>
    </div>

    <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $palette }}">
        {{ __('website.booking.status.'.$booking->status) }}
    </span>
</div>

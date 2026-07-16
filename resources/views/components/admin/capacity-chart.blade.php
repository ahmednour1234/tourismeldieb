@props(['days', 'peak'])

{{--
    Seat capacity per day — a single-series column chart.

    Colour: brand teal, stepped for each surface and validated with the dataviz
    palette checker (ordinal ramp, one hue, monotone lightness, light end
    clearing 2:1 against its own surface):
      light on #ffffff → teal 500..900
      dark  on #0f172a → teal 300..700
    Do not lighten the light-mode steps: teal-400 measures 1.86:1 and fails.

    A single series carries no legend — the section title names what is plotted.
--}}
@php
    $peak = max(1, (int) $peak);
    $hasData = collect($days)->contains(fn (array $day): bool => $day['seats'] > 0);
@endphp

<div
    class="capacity-chart"
    x-data="{ active: null }"
>
    <style>
        .capacity-chart {
            --bar: #0f766e;          /* teal-700  — light surface (#fff) */
            --bar-hover: #134e4a;    /* teal-900 */
            --track: #f1f5f9;        /* slate-100 */
            --rule: #e2e8f0;         /* slate-200 */
        }
        :is(.dark) .capacity-chart {
            --bar: #2dd4bf;          /* teal-400  — dark surface (#0f172a) */
            --bar-hover: #5eead4;    /* teal-300 */
            --track: #1e293b;        /* slate-800 */
            --rule: #334155;         /* slate-700 */
        }
    </style>

    @if (! $hasData)
        <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('admin.dashboard.schedule_empty', ['days' => count($days)]) }}
        </p>
    @else
        {{-- Plot. Bars are capped at 24px and separated by a 2px surface gap;
             the leftover band width is deliberate air, not padding. --}}
        <div class="relative">
            <div class="flex h-48 items-end gap-[2px]" role="presentation">
                @foreach ($days as $index => $day)
                    @php
                        $height = $day['seats'] > 0 ? max(2, (int) round($day['seats'] / $peak * 100)) : 0;
                    @endphp
                    <div
                        class="group relative flex h-full max-w-[24px] flex-1 items-end"
                        x-on:mouseenter="active = {{ $index }}"
                        x-on:mouseleave="active = null"
                        x-on:focus="active = {{ $index }}"
                        x-on:blur="active = null"
                        tabindex="0"
                        role="img"
                        aria-label="{{ $day['seats'] > 0
                            ? __('admin.dashboard.seats_on', ['count' => $day['seats'], 'date' => $day['label']])
                            : __('admin.dashboard.no_departures_on', ['date' => $day['label']]) }}"
                    >
                        {{-- Track: shows the day exists even at zero, so gaps in
                             the schedule stay visible rather than collapsing. --}}
                        <div class="absolute inset-x-0 bottom-0 h-full rounded-sm" style="background: var(--track)"></div>

                        @if ($height > 0)
                            <div
                                class="relative w-full rounded-t transition-[background-color]"
                                style="height: {{ $height }}%; background: var(--bar)"
                                x-bind:style="active === {{ $index }} ? 'height: {{ $height }}%; background: var(--bar-hover)' : 'height: {{ $height }}%; background: var(--bar)'"
                            ></div>
                        @endif

                        {{-- Tooltip. Anchored to the bar's top edge rather than
                             the column, so a full-height bar cannot push it out
                             of the card and over the heading. --}}
                        <div
                            x-cloak
                            x-show="active === {{ $index }}"
                            class="pointer-events-none absolute start-1/2 z-20 w-max -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white shadow-lg rtl:translate-x-1/2 dark:bg-slate-700"
                            style="bottom: min({{ $height }}%, calc(100% - 2.75rem)); margin-bottom: 0.5rem;"
                        >
                            <span class="block font-semibold">{{ $day['label'] }}</span>
                            <span class="block text-slate-300">
                                {{ $day['seats'] }} {{ Str::lower(__('admin.dashboard.total_seats')) }}
                                @if ($day['departures'] > 0)
                                    &middot; {{ $day['departures'] }} {{ Str::lower(__('admin.dashboard.total_departures')) }}
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Baseline: hairline, solid, recessive. --}}
            <div class="mt-0 h-px w-full" style="background: var(--rule)"></div>
        </div>

        {{-- Axis: label the ends and the peak only — never every point. --}}
        <div class="mt-2 flex justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>{{ $days[0]['label'] }}</span>
            <span>{{ $days[count($days) - 1]['label'] }}</span>
        </div>
    @endif
</div>

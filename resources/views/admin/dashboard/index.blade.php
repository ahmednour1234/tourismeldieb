<x-layouts.admin :title="__('admin.dashboard.title')">
    <x-admin.page-header :title="__('admin.dashboard.title')" />

    {{-- KPI tiles. Each links to the resource it counts. --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($stats as $stat)
            <x-admin.stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :href="$stat['href']"
                :hint="$stat['hint']"
            />
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Capacity chart --}}
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.dashboard.schedule_title') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('admin.dashboard.schedule_subtitle', ['days' => count($schedule['days'])]) }}
                    </p>
                </div>
                <dl class="flex gap-6 text-end">
                    <div>
                        <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.total_seats') }}</dt>
                        <dd class="text-xl font-bold text-slate-950 tabular-nums dark:text-white">{{ number_format($schedule['totalSeats']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.total_departures') }}</dt>
                        <dd class="text-xl font-bold text-slate-950 tabular-nums dark:text-white">{{ number_format($schedule['totalDepartures']) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6">
                <x-admin.capacity-chart :days="$schedule['days']" :peak="$schedule['peakSeats']" />
            </div>
        </section>

        {{-- Activity feed --}}
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.nav.activity_log') }}</h2>

            @if ($recentActivity !== [])
                <ul class="mt-4 space-y-4">
                    @foreach ($recentActivity as $activity)
                        <li class="flex gap-3 text-sm">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-teal-600 dark:bg-teal-400"></span>
                            <div class="min-w-0">
                                <p class="text-slate-700 dark:text-slate-200">
                                    <span class="font-medium">{{ $activity['subject'] ?? $activity['description'] }}</span>
                                    @if ($activity['event'])
                                        <span class="text-slate-500 dark:text-slate-400">&middot; {{ $activity['event'] }}</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                    {{ $activity['causer'] ? $activity['causer'].' · ' : '' }}{{ $activity['ago'] }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-4 rounded-md border border-dashed border-slate-200 p-6 text-center dark:border-slate-700">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.no_activity') }}</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('admin.dashboard.activity_hint') }}</p>
                </div>
            @endif
        </section>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        {{-- Latest tours --}}
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-baseline justify-between">
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.dashboard.recent_tours') }}</h2>
                @can('tours.view')
                    <a href="{{ route('admin.tours.index') }}" class="text-sm font-semibold text-teal-700 hover:underline dark:text-teal-400">
                        {{ __('admin.dashboard.view_all') }}
                    </a>
                @endcan
            </div>

            <div class="mt-4 space-y-2">
                @forelse ($recentTours as $tour)
                    <a
                        href="{{ route('admin.tours.show', $tour['id']) }}"
                        class="flex items-center justify-between gap-3 rounded-md border border-slate-200 p-3 transition hover:border-teal-600 dark:border-slate-800 dark:hover:border-teal-500"
                    >
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $tour['name'] }}</span>
                            @if ($tour['destination'])
                                <span class="block truncate text-xs text-slate-400 dark:text-slate-500">{{ $tour['destination'] }}</span>
                            @endif
                        </span>
                        <x-admin.badge>{{ $tour['status'] }}</x-admin.badge>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('admin.crud.empty') }}</p>
                @endforelse
            </div>
        </section>

        {{-- Needs attention: the only actionable list on the page. --}}
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.dashboard.needs_attention') }}</h2>

            <div class="mt-4 space-y-2">
                @forelse ($incomplete as $tour)
                    <a
                        href="{{ route('admin.tours.edit', $tour['id']) }}"
                        class="flex items-center justify-between gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 transition hover:border-amber-400 dark:border-amber-900/50 dark:bg-amber-950/20 dark:hover:border-amber-700"
                    >
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $tour['name'] }}</span>
                            <span class="block text-xs text-amber-700 dark:text-amber-500">{{ $tour['reason'] }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-amber-600 rtl:rotate-180 dark:text-amber-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @empty
                    <div class="rounded-md border border-dashed border-slate-200 p-6 text-center dark:border-slate-700">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin.dashboard.all_complete') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.admin>

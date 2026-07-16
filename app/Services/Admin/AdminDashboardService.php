<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\Language;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Reads the admin dashboard's summary numbers.
 *
 * Every figure here is a real query. Earlier revisions fell back to hardcoded
 * counts when a query threw, which meant a broken database rendered a dashboard
 * full of confident-looking wrong numbers. A failure must surface, not be
 * papered over.
 */
final class AdminDashboardService
{
    private const SCHEDULE_DAYS = 30;

    private const RECENT_LIMIT = 5;

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'stats' => $this->stats(),
            'schedule' => $this->schedule(),
            'recentActivity' => $this->recentActivity(),
            'recentTours' => $this->recentTours(),
            'incomplete' => $this->incompleteTours(),
        ];
    }

    /**
     * Headline counts. `published` doubles as the denominator context for the
     * draft figure, so both are always shown together.
     *
     * @return list<array{label: string, value: int, href: string|null, hint: string|null}>
     */
    private function stats(): array
    {
        $tours = Tour::query()->count();
        $published = Tour::query()->published()->count();

        return [
            [
                'label' => __('admin.stats.tours'),
                'value' => $tours,
                'href' => route('admin.tours.index'),
                'hint' => __('admin.stats.published_of_total', ['count' => $published, 'total' => $tours]),
            ],
            [
                'label' => __('admin.stats.destinations'),
                'value' => Destination::query()->count(),
                'href' => route('admin.destinations.index'),
                'hint' => __('admin.stats.active_count', ['count' => Destination::query()->active()->count()]),
            ],
            [
                'label' => __('admin.stats.categories'),
                'value' => TourCategory::query()->count(),
                'href' => route('admin.categories.index'),
                'hint' => __('admin.stats.active_count', ['count' => TourCategory::query()->active()->count()]),
            ],
            [
                'label' => __('admin.stats.countries'),
                'value' => Country::query()->count(),
                'href' => route('admin.countries.index'),
                'hint' => null,
            ],
            [
                'label' => __('admin.stats.languages'),
                'value' => Language::query()->count(),
                'href' => route('admin.languages.index'),
                'hint' => __('admin.stats.active_count', ['count' => Language::query()->active()->count()]),
            ],
            [
                'label' => __('admin.stats.currencies'),
                'value' => Currency::query()->count(),
                'href' => route('admin.currencies.index'),
                'hint' => __('admin.stats.active_count', ['count' => Currency::query()->active()->count()]),
            ],
        ];
    }

    /**
     * Seat capacity per day across the next 30 days.
     *
     * Days with no departures are returned as explicit zeroes so the chart shows
     * gaps in the schedule rather than silently compressing them away.
     *
     * @return array{days: list<array{date: string, label: string, departures: int, seats: int, booked: int}>, totalSeats: int, totalDepartures: int, peakSeats: int}
     */
    private function schedule(): array
    {
        $from = Carbon::today();
        $to = $from->copy()->addDays(self::SCHEDULE_DAYS - 1);

        // Grouped on the stored `departure_date` rather than date(start_datetime):
        // it is indexed, so this stays a range scan instead of a full-table
        // function scan as the schedule grows.
        /** @var Collection<string, object> $rows */
        $rows = DB::table('tour_departures')
            ->select('departure_date')
            ->selectRaw('count(*) as departures')
            ->selectRaw('coalesce(sum(capacity), 0) as seats')
            ->selectRaw('coalesce(sum(reserved_capacity + confirmed_capacity), 0) as booked')
            ->whereBetween('departure_date', [$from->toDateString(), $to->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->groupBy('departure_date')
            ->get()
            ->keyBy(fn (object $row): string => Carbon::parse($row->departure_date)->toDateString());

        $days = [];

        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $row = $rows->get($key);

            $days[] = [
                'date' => $key,
                'label' => $cursor->isoFormat('D MMM'),
                'departures' => (int) ($row->departures ?? 0),
                'seats' => (int) ($row->seats ?? 0),
                'booked' => (int) ($row->booked ?? 0),
            ];
        }

        $seats = array_column($days, 'seats');

        return [
            'days' => $days,
            'totalSeats' => array_sum($seats),
            'totalDepartures' => array_sum(array_column($days, 'departures')),
            'peakSeats' => $seats === [] ? 0 : max($seats),
        ];
    }

    /**
     * @return list<array{description: string, subject: string|null, causer: string|null, event: string|null, at: string, ago: string}>
     */
    private function recentActivity(): array
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->latest()
            ->limit(self::RECENT_LIMIT)
            ->get()
            ->map(fn (Activity $activity): array => [
                'description' => $activity->description,
                'subject' => $this->subjectLabel($activity),
                'causer' => $activity->causer?->getAttribute('name'),
                'event' => $activity->event,
                'at' => $activity->created_at?->toDayDateTimeString() ?? '',
                'ago' => $activity->created_at?->diffForHumans() ?? '',
            ])
            ->all();
    }

    private function subjectLabel(Activity $activity): ?string
    {
        $subject = $activity->subject;

        if ($subject === null) {
            return null;
        }

        return $subject->getAttribute('translation')?->name
            ?? $subject->getAttribute('name')
            ?? $subject->getAttribute('code')
            ?? class_basename($subject).' #'.$subject->getKey();
    }

    /**
     * @return list<array{id: int, name: string, status: string, destination: string|null}>
     */
    private function recentTours(): array
    {
        return Tour::query()
            ->with(['translation', 'destination.translation'])
            ->latest('id')
            ->limit(self::RECENT_LIMIT)
            ->get()
            ->map(fn (Tour $tour): array => [
                'id' => $tour->id,
                'name' => $tour->translation?->name ?? $tour->code,
                'status' => $tour->status,
                'destination' => $tour->destination?->translation?->name ?? $tour->destination?->code,
            ])
            ->all();
    }

    /**
     * Tours that cannot go live yet — the dashboard's only actionable list.
     * A tour missing a translation for an active locale would render blank on
     * the public site, so surface it here rather than let it ship broken.
     *
     * @return list<array{id: int, name: string, reason: string}>
     */
    private function incompleteTours(): array
    {
        $activeLocales = Language::query()->active()->pluck('code');
        $expected = $activeLocales->count();

        if ($expected === 0) {
            return [];
        }

        // The translation count is compared in the WHERE clause via a subquery
        // rather than HAVING on a withCount() alias: PostgreSQL cannot resolve a
        // select alias inside HAVING, so that form throws "column does not exist".
        return Tour::query()
            ->with('translation')
            ->withCount(['translations' => fn ($query) => $query->whereIn('locale', $activeLocales)])
            ->whereRaw(
                '(select count(*) from tour_translations'.
                ' where tour_translations.tour_id = tours.id'.
                ' and tour_translations.locale in ('.rtrim(str_repeat('?,', $expected), ',').')) < ?',
                [...$activeLocales->all(), $expected],
            )
            ->limit(self::RECENT_LIMIT)
            ->get()
            ->map(function (Tour $tour) use ($expected): array {
                $missing = $expected - (int) $tour->getAttribute('translations_count');

                return [
                    'id' => $tour->id,
                    'name' => $tour->translation?->name ?? $tour->code,
                    // trans_choice, not __: the message has plural forms, and
                    // Arabic needs its dual / 3-10 / 11+ branches selected.
                    'reason' => trans_choice('admin.dashboard.missing_translations', $missing, ['count' => $missing]),
                ];
            })
            ->all();
    }
}

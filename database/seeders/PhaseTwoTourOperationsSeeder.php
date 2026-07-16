<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhaseTwoTourOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $adminId = (int) DB::table('users')->where('email', 'admin@hurgadaguide.example')->value('id');
        $usdId = $this->id('currencies', 'code', 'USD');
        $eurId = $this->id('currencies', 'code', 'EUR');
        $egpId = $this->id('currencies', 'code', 'EGP');

        $orangeBayId = $this->id('tours', 'code', 'orange-bay');
        $quadSafariId = $this->id('tours', 'code', 'quad-bike-safari');
        $luxorId = $this->id('tours', 'code', 'luxor-full-day');

        $this->seedOptions($orangeBayId, [
            ['code' => 'shared-boat-trip', 'capacity' => 30, 'maximum_guests' => 30, 'maximum_booking_quantity' => 10, 'duration_value' => 8, 'is_default' => true, 'is_private' => false, 'sort_order' => 1, 'en' => ['name' => 'Shared Boat Trip', 'slug' => 'shared-boat-trip'], 'ar' => ['name' => 'رحلة قارب مشتركة', 'slug' => 'shared-boat-trip']],
            ['code' => 'vip-boat-trip', 'capacity' => 14, 'maximum_guests' => 14, 'maximum_booking_quantity' => 8, 'duration_value' => 8, 'is_default' => false, 'is_private' => false, 'sort_order' => 2, 'en' => ['name' => 'VIP Boat Trip', 'slug' => 'vip-boat-trip'], 'ar' => ['name' => 'رحلة قارب فاخرة', 'slug' => 'vip-boat-trip']],
            ['code' => 'private-yacht', 'capacity' => 10, 'maximum_guests' => 10, 'maximum_booking_quantity' => 1, 'duration_value' => 8, 'is_default' => false, 'is_private' => true, 'sort_order' => 3, 'en' => ['name' => 'Private Yacht', 'slug' => 'private-yacht'], 'ar' => ['name' => 'يخت خاص', 'slug' => 'private-yacht']],
        ], $adminId, $now);

        $this->seedOptions($quadSafariId, [
            ['code' => 'single-quad', 'capacity' => 20, 'maximum_guests' => 20, 'maximum_booking_quantity' => 10, 'duration_value' => 5, 'is_default' => true, 'is_private' => false, 'sort_order' => 1, 'en' => ['name' => 'Single Quad', 'slug' => 'single-quad'], 'ar' => ['name' => 'دباب فردي', 'slug' => 'single-quad']],
            ['code' => 'double-quad', 'capacity' => 20, 'maximum_guests' => 20, 'maximum_booking_quantity' => 10, 'duration_value' => 5, 'is_default' => false, 'is_private' => false, 'sort_order' => 2, 'en' => ['name' => 'Double Quad', 'slug' => 'double-quad'], 'ar' => ['name' => 'دباب مزدوج', 'slug' => 'double-quad']],
            ['code' => 'private-safari', 'capacity' => 8, 'maximum_guests' => 8, 'maximum_booking_quantity' => 1, 'duration_value' => 5, 'is_default' => false, 'is_private' => true, 'sort_order' => 3, 'en' => ['name' => 'Private Safari', 'slug' => 'private-safari'], 'ar' => ['name' => 'سفاري خاص', 'slug' => 'private-safari']],
        ], $adminId, $now);

        $this->seedOptions($luxorId, [
            ['code' => 'shared-bus', 'capacity' => 42, 'maximum_guests' => 42, 'maximum_booking_quantity' => 8, 'duration_value' => 12, 'is_default' => true, 'is_private' => false, 'sort_order' => 1, 'en' => ['name' => 'Shared Bus', 'slug' => 'shared-bus'], 'ar' => ['name' => 'أتوبيس مشترك', 'slug' => 'shared-bus']],
            ['code' => 'small-group', 'capacity' => 12, 'maximum_guests' => 12, 'maximum_booking_quantity' => 8, 'duration_value' => 12, 'is_default' => false, 'is_private' => false, 'sort_order' => 2, 'en' => ['name' => 'Small Group', 'slug' => 'small-group'], 'ar' => ['name' => 'مجموعة صغيرة', 'slug' => 'small-group']],
            ['code' => 'private-car', 'capacity' => 4, 'maximum_guests' => 4, 'maximum_booking_quantity' => 1, 'duration_value' => 12, 'is_default' => false, 'is_private' => true, 'sort_order' => 3, 'en' => ['name' => 'Private Car', 'slug' => 'private-car'], 'ar' => ['name' => 'سيارة خاصة', 'slug' => 'private-car']],
        ], $adminId, $now);

        $this->seedSchedulesAndDepartures($adminId, $now);
        $this->seedPrices($usdId, $adminId, $now);
        $this->seedPricingRules($orangeBayId, $quadSafariId, $luxorId, $usdId, $adminId, $now);
        $this->seedCurrencyRates($usdId, $eurId, $egpId, $adminId, $now);
        $this->seedCoupons($orangeBayId, $quadSafariId, $luxorId, $usdId, $adminId, $now);
        $this->seedBlackoutDate($orangeBayId, $adminId, $now);
    }

    /**
     * @param  list<array<string, mixed>>  $options
     */
    private function seedOptions(int $tourId, array $options, int $adminId, mixed $now): void
    {
        foreach ($options as $option) {
            DB::table('tour_options')->updateOrInsert(
                ['tour_id' => $tourId, 'code' => $option['code']],
                [
                    'capacity' => $option['capacity'],
                    'minimum_guests' => 1,
                    'maximum_guests' => $option['maximum_guests'],
                    'minimum_booking_quantity' => 1,
                    'maximum_booking_quantity' => $option['maximum_booking_quantity'],
                    'duration_value' => $option['duration_value'],
                    'duration_unit' => 'hour',
                    'is_private' => $option['is_private'],
                    'requires_manual_confirmation' => $option['is_private'],
                    'is_default' => $option['is_default'],
                    'is_active' => true,
                    'sort_order' => $option['sort_order'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $optionId = (int) DB::table('tour_options')
                ->where('tour_id', $tourId)
                ->where('code', $option['code'])
                ->value('id');

            foreach (['en', 'ar'] as $locale) {
                DB::table('tour_option_translations')->updateOrInsert(
                    ['tour_option_id' => $optionId, 'locale' => $locale],
                    [
                        'tour_id' => $tourId,
                        'name' => $option[$locale]['name'],
                        'slug' => $option[$locale]['slug'],
                        'short_description' => $this->optionCopy($option['code']),
                        'description' => $this->optionCopy($option['code'], true),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    }

    private function seedSchedulesAndDepartures(int $adminId, mixed $now): void
    {
        $scheduleMap = [
            'orange-bay' => [['sunday', '08:00'], ['tuesday', '08:00'], ['thursday', '08:00']],
            'quad-bike-safari' => [['monday', '15:00'], ['wednesday', '15:00'], ['friday', '15:00']],
            'luxor-full-day' => [['monday', '05:00'], ['thursday', '05:00']],
        ];

        foreach ($scheduleMap as $tourCode => $schedules) {
            $optionIds = DB::table('tour_options')
                ->join('tours', 'tours.id', '=', 'tour_options.tour_id')
                ->where('tours.code', $tourCode)
                ->pluck('tour_options.id');

            foreach ($optionIds as $optionId) {
                $option = DB::table('tour_options')->where('id', $optionId)->first();

                foreach ($schedules as [$day, $startTime]) {
                    $endTime = CarbonImmutable::createFromFormat('H:i', $startTime)
                        ->addHours((int) $option->duration_value)
                        ->format('H:i');

                    DB::table('tour_schedules')->updateOrInsert(
                        ['tour_option_id' => $optionId, 'day_of_week' => $day, 'start_time' => $startTime.':00'],
                        [
                            'end_time' => $endTime.':00',
                            'capacity_override' => null,
                            'pickup_start_time' => $this->pickupTime($startTime),
                            'booking_cutoff_hours' => 24,
                            'booking_opens_days_before' => 90,
                            'valid_from' => '2026-01-01',
                            'valid_to' => null,
                            'is_active' => true,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ],
                    );

                    $scheduleId = (int) DB::table('tour_schedules')
                        ->where('tour_option_id', $optionId)
                        ->where('day_of_week', $day)
                        ->where('start_time', $startTime.':00')
                        ->value('id');

                    $this->seedDepartures($option, $scheduleId, $day, $startTime, $adminId, $now);
                }
            }
        }
    }

    private function seedDepartures(object $option, int $scheduleId, string $day, string $startTime, int $adminId, mixed $now): void
    {
        $firstDate = CarbonImmutable::now('Africa/Cairo')->next($day);

        for ($week = 0; $week < 4; $week++) {
            $startLocal = $firstDate->addWeeks($week)->setTimeFromTimeString($startTime);
            $endLocal = $startLocal->addHours((int) $option->duration_value);
            $startUtc = $startLocal->utc();
            $endUtc = $endLocal->utc();
            $capacity = (int) $option->capacity;

            DB::table('tour_departures')->updateOrInsert(
                ['tour_option_id' => $option->id, 'start_datetime' => $startUtc->toDateTimeString()],
                [
                    'tour_schedule_id' => $scheduleId,
                    'departure_date' => $startLocal->toDateString(),
                    'end_datetime' => $endUtc->toDateTimeString(),
                    'capacity' => $capacity,
                    'reserved_capacity' => 0,
                    'confirmed_capacity' => 0,
                    'available_capacity' => $capacity,
                    'status' => 'available',
                    'booking_opens_at' => $startUtc->subDays(90)->toDateTimeString(),
                    'booking_cutoff_at' => $startUtc->subHours(24)->toDateTimeString(),
                    'manual_notes' => null,
                    'generated_automatically' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedPrices(int $usdId, int $adminId, mixed $now): void
    {
        $prices = [
            'shared-boat-trip' => [['adult', 2500], ['child', 1500], ['infant', 0]],
            'vip-boat-trip' => [['adult', 4500], ['child', 2500], ['infant', 0]],
            'private-yacht' => [['private_group', 35000]],
            'single-quad' => [['adult', 3500], ['child', 2500]],
            'double-quad' => [['adult', 3000], ['child', 2000]],
            'private-safari' => [['private_group', 22000]],
            'shared-bus' => [['adult', 8500], ['child', 5500], ['infant', 0]],
            'small-group' => [['adult', 12500], ['child', 8000], ['infant', 0]],
            'private-car' => [['private_group', 42000]],
        ];

        foreach ($prices as $optionCode => $lines) {
            $optionId = $this->id('tour_options', 'code', $optionCode);

            foreach ($lines as [$guestType, $amountMinor]) {
                DB::table('tour_prices')->updateOrInsert(
                    ['tour_option_id' => $optionId, 'guest_type' => $guestType, 'currency_id' => $usdId, 'valid_from' => '2026-01-01'],
                    [
                        'amount_minor' => $amountMinor,
                        'minimum_quantity' => 1,
                        'maximum_quantity' => null,
                        'valid_to' => null,
                        'is_active' => true,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    }

    private function seedPricingRules(int $orangeBayId, int $quadSafariId, int $luxorId, int $usdId, int $adminId, mixed $now): void
    {
        $rules = [
            [$orangeBayId, null, 'Summer island demand', 'seasonal', 'percentage_increase', 1500, null, 10, ['friday', 'saturday'], null],
            [$orangeBayId, $this->id('tour_options', 'code', 'shared-boat-trip'), 'Early booking shared boat', 'early_booking', 'percentage_discount', 1000, null, 20, null, 7],
            [$quadSafariId, null, 'Weekend safari increase', 'weekend', 'percentage_increase', 800, null, 15, ['friday', 'saturday'], null],
            [$quadSafariId, null, 'Group safari discount', 'group', 'percentage_discount', 1200, null, 30, null, null],
            [$luxorId, null, 'Private car manual offer', 'manual_offer', 'fixed_discount', 5000, $usdId, 5, null, null],
        ];

        foreach ($rules as [$tourId, $optionId, $name, $ruleType, $adjustmentType, $adjustmentValue, $currencyId, $priority, $days, $minDaysBefore]) {
            DB::table('pricing_rules')->updateOrInsert(
                ['tour_id' => $tourId, 'name' => $name],
                [
                    'tour_option_id' => $optionId,
                    'rule_type' => $ruleType,
                    'adjustment_type' => $adjustmentType,
                    'adjustment_value' => $adjustmentValue,
                    'currency_id' => $currencyId,
                    'priority' => $priority,
                    'starts_at' => CarbonImmutable::now()->startOfYear()->toDateTimeString(),
                    'ends_at' => CarbonImmutable::now()->endOfYear()->toDateTimeString(),
                    'days_of_week' => $days === null ? null : json_encode($days, JSON_THROW_ON_ERROR),
                    'minimum_guests' => $ruleType === 'group' ? 4 : null,
                    'maximum_guests' => null,
                    'minimum_days_before' => $minDaysBefore,
                    'maximum_days_before' => null,
                    'applies_to_guest_type' => null,
                    'stacking_mode' => $ruleType === 'manual_offer' ? 'exclusive' : 'stackable',
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedCurrencyRates(int $usdId, int $eurId, int $egpId, int $adminId, mixed $now): void
    {
        foreach ([[$usdId, $eurId, '0.9200000000'], [$usdId, $egpId, '48.5000000000'], [$eurId, $usdId, '1.0869565217'], [$egpId, $usdId, '0.0206185567']] as [$baseId, $targetId, $rate]) {
            DB::table('currency_rates')->updateOrInsert(
                ['base_currency_id' => $baseId, 'target_currency_id' => $targetId, 'effective_at' => CarbonImmutable::now()->startOfDay()->toDateTimeString()],
                [
                    'rate' => $rate,
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedCoupons(int $orangeBayId, int $quadSafariId, int $luxorId, int $usdId, int $adminId, mixed $now): void
    {
        $coupons = [
            ['code' => 'SUNNY10', 'name' => 'Sunny 10 percent', 'discount_type' => 'percentage', 'discount_value' => 1000, 'currency_id' => null, 'maximum_discount_minor' => 5000, 'minimum_order_minor' => 5000, 'tour_ids' => [$orangeBayId, $quadSafariId]],
            ['code' => 'FAMILY25', 'name' => 'Family fixed discount', 'discount_type' => 'fixed', 'discount_value' => 2500, 'currency_id' => $usdId, 'maximum_discount_minor' => null, 'minimum_order_minor' => 10000, 'tour_ids' => [$orangeBayId, $luxorId]],
        ];

        foreach ($coupons as $coupon) {
            DB::table('coupons')->updateOrInsert(
                ['code' => $coupon['code']],
                [
                    'name' => $coupon['name'],
                    'discount_type' => $coupon['discount_type'],
                    'discount_value' => $coupon['discount_value'],
                    'currency_id' => $coupon['currency_id'],
                    'maximum_discount_minor' => $coupon['maximum_discount_minor'],
                    'minimum_order_minor' => $coupon['minimum_order_minor'],
                    'usage_limit' => 500,
                    'usage_limit_per_customer' => 1,
                    'used_count' => 0,
                    'starts_at' => CarbonImmutable::now()->startOfYear()->toDateTimeString(),
                    'expires_at' => CarbonImmutable::now()->endOfYear()->toDateTimeString(),
                    'is_active' => true,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $couponId = $this->id('coupons', 'code', $coupon['code']);
            foreach ($coupon['tour_ids'] as $tourId) {
                DB::table('coupon_tour')->updateOrInsert(
                    ['coupon_id' => $couponId, 'tour_id' => $tourId],
                    ['updated_at' => $now, 'created_at' => $now],
                );
            }
        }
    }

    private function seedBlackoutDate(int $orangeBayId, int $adminId, mixed $now): void
    {
        $startDate = CarbonImmutable::now('Africa/Cairo')->addWeeks(6)->startOfWeek()->toDateString();

        DB::table('tour_blackout_dates')->updateOrInsert(
            ['tour_id' => $orangeBayId, 'start_date' => $startDate, 'end_date' => $startDate],
            [
                'tour_option_id' => null,
                'reason' => 'Marine maintenance day',
                'internal_notes' => 'Demo blackout date for Phase 2 availability testing.',
                'cancel_existing_departures' => false,
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    private function optionCopy(string $code, bool $long = false): string
    {
        $copy = [
            'shared-boat-trip' => 'A reliable shared option with transfers, lunch, and guided snorkeling.',
            'vip-boat-trip' => 'A smaller group boat day with more comfort and a relaxed pace.',
            'private-yacht' => 'A private group yacht experience with flexible timing.',
            'single-quad' => 'One guest per quad for a classic desert ride.',
            'double-quad' => 'Two guests sharing one quad with guided desert stops.',
            'private-safari' => 'A private desert route for families or small groups.',
            'shared-bus' => 'A full-day cultural route by shared coach.',
            'small-group' => 'A smaller Luxor day with more flexible pacing.',
            'private-car' => 'A private vehicle and tailored timing for the Luxor route.',
        ][$code] ?? 'A bookable tour option.';

        return $long ? $copy.' Availability, capacity, and prices are managed from the Phase 2 tables.' : $copy;
    }

    private function pickupTime(string $startTime): string
    {
        return CarbonImmutable::createFromFormat('H:i', $startTime)->subMinutes(45)->format('H:i:s');
    }

    private function id(string $table, string $column, string $value): int
    {
        return (int) DB::table($table)->where($column, $value)->value('id');
    }
}

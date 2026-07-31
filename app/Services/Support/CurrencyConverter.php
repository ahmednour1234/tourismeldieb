<?php

declare(strict_types=1);

namespace App\Services\Support;

use Illuminate\Support\Facades\DB;

/**
 * Converts prices from their stored currency to the currency the visitor
 * selected in the header.
 *
 * Every tour price is stored in one base currency (USD today), but the site
 * offers USD/EUR/EGP. Without conversion, picking EGP changed the label's
 * currency code but not the number — so the switcher looked broken. Rates live
 * in `currency_rates`; this reads them, caches them for the request, and rounds
 * deterministically to the target currency's minor units.
 */
final class CurrencyConverter
{
    /** @var array<string, float>|null */
    private ?array $rates = null;

    /**
     * @return array{amount_minor: int, code: string, symbol: string, decimal_places: int}|null
     */
    public function convertMinor(int $amountMinor, string $fromCode, string $toCode): ?array
    {
        $target = $this->currency($toCode);

        if ($target === null) {
            return null;
        }

        $fromCode = mb_strtoupper($fromCode);
        $toCode = mb_strtoupper($toCode);

        // Same currency: no rate lookup, no rounding drift.
        if ($fromCode === $toCode) {
            return [
                'amount_minor' => $amountMinor,
                'code' => $target->code,
                'symbol' => $target->symbol,
                'decimal_places' => (int) $target->decimal_places,
            ];
        }

        $rate = $this->rate($fromCode, $toCode);

        if ($rate === null) {
            return null;
        }

        $source = $this->currency($fromCode);

        if ($source === null) {
            return null;
        }

        // Work in major units through the multiply, then re-scale to the
        // target's own minor units — EGP and USD both use 2, but a currency
        // with a different decimal_places must not inherit the source's scale.
        $major = $amountMinor / (10 ** (int) $source->decimal_places);
        $convertedMajor = $major * $rate;
        $convertedMinor = (int) round($convertedMajor * (10 ** (int) $target->decimal_places));

        return [
            'amount_minor' => $convertedMinor,
            'code' => $target->code,
            'symbol' => $target->symbol,
            'decimal_places' => (int) $target->decimal_places,
        ];
    }

    private function currency(string $code): ?object
    {
        return DB::table('currencies')
            ->where('code', mb_strtoupper($code))
            ->whereNull('deleted_at')
            ->select(['code', 'symbol', 'decimal_places'])
            ->first();
    }

    /**
     * The active rate from → to, most recent effective_at first. Loaded once
     * per request into a code-pair map.
     */
    private function rate(string $from, string $to): ?float
    {
        if ($this->rates === null) {
            $this->rates = $this->loadRates();
        }

        return $this->rates[$from.'>'.$to] ?? null;
    }

    /**
     * @return array<string, float>
     */
    private function loadRates(): array
    {
        return DB::table('currency_rates')
            ->join('currencies as base', 'base.id', '=', 'currency_rates.base_currency_id')
            ->join('currencies as target', 'target.id', '=', 'currency_rates.target_currency_id')
            ->where('currency_rates.is_active', true)
            ->whereNull('currency_rates.deleted_at')
            ->orderByDesc('currency_rates.effective_at')
            ->select(['base.code as from_code', 'target.code as to_code', 'currency_rates.rate'])
            ->get()
            ->reduce(function (array $carry, object $row): array {
                $key = $row->from_code.'>'.$row->to_code;

                // First wins — the query is ordered newest-first, so this keeps
                // the most recent rate for each pair.
                $carry[$key] ??= (float) $row->rate;

                return $carry;
            }, []);
    }
}

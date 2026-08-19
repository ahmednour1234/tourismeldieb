<?php

declare(strict_types=1);

namespace App\Admin;

use App\Models\Currency;

/**
 * Converts between the amount an admin types and the integer the database
 * stores.
 *
 * Prices are stored in minor units (an integer number of cents) so that no
 * arithmetic in the booking or currency-conversion path ever runs on a float.
 * Nobody wants to *type* cents, though — a form asking for "1950" to mean
 * $19.50 gets a $1,950 tour sold for $19.50 sooner or later. So the `money`
 * field type takes a major-unit amount, and this class is the single place the
 * two representations meet.
 *
 * How many minor units make a major one is per-currency: JPY has none, so its
 * amount is already an integer, and KWD has three. The scale therefore comes
 * from the row's own currency rather than a hardcoded 100.
 */
final class Money
{
    /**
     * Decimal places per currency id, memoised for the request.
     *
     * A form saves one row at a time, but the listing formats a whole page of
     * them; without this that is one query per row.
     *
     * @var array<int, int>|null
     */
    private static ?array $decimals = null;

    /**
     * "19.50" as typed, in USD, becomes 1950.
     *
     * Rounds rather than truncates: a currency conversion elsewhere can hand
     * back 19.499999999, and truncation would quietly shave a cent off it.
     */
    public static function toMinor(int|float|string $major, int|string|null $currencyId): int
    {
        $scale = 10 ** self::decimalPlaces($currencyId);

        return (int) round(((float) $major) * $scale);
    }

    /**
     * 1950 stored, in USD, becomes "19.50" for the form.
     *
     * Returned as a string with the currency's exact number of decimals so the
     * value round-trips through the form unchanged — a float 19.5 would render
     * as "19.5" and read as a different number than what was saved.
     */
    public static function toMajor(int|float|string|null $minor, int|string|null $currencyId): ?string
    {
        if ($minor === null || $minor === '') {
            return null;
        }

        $places = self::decimalPlaces($currencyId);

        return number_format((int) $minor / (10 ** $places), $places, '.', '');
    }

    /**
     * The decimal places for a currency, defaulting to 2 for an unknown one.
     */
    public static function decimalPlaces(int|string|null $currencyId): int
    {
        if ($currencyId === null || $currencyId === '') {
            return 2;
        }

        $currencyId = (int) $currencyId;

        self::$decimals ??= Currency::query()
            ->pluck('decimal_places', 'id')
            ->map(fn (mixed $places): int => (int) $places)
            ->all();

        return self::$decimals[$currencyId] ?? 2;
    }

    /**
     * Drop the memoised table — the currencies admin can change a currency's
     * decimal places within the same request that later formats a price.
     */
    public static function flush(): void
    {
        self::$decimals = null;
    }
}

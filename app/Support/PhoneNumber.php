<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Joins and splits the two halves of the phone field.
 *
 * The form collects a dialling code and a local number separately, but
 * `booking_requests.customer_phone` is a single column and every consumer of it
 * — the admin listing, the confirmation email, the `tel:` link — wants one
 * dialable string. Rather than migrating a second column onto a table the
 * operator already has data in, the two halves are joined on the way in and
 * taken apart again only when the form has to be redrawn.
 *
 * That split is best-effort by nature: a number typed before this field existed
 * has no recorded code. Such a number is returned whole as the local part, so
 * it is redisplayed exactly as the customer typed it rather than being silently
 * reattributed to whichever country happens to match its first digits.
 */
final class PhoneNumber
{
    /**
     * Combine a dialling code and a local number into one stored value.
     *
     * Returns null for a blank local number even when a code is selected: the
     * select always has something chosen, so storing "+49" on its own would
     * turn an empty optional field into a phone number that cannot be called.
     */
    public static function join(?string $code, ?string $local): ?string
    {
        $local = self::tidy($local);

        if ($local === null) {
            return null;
        }

        $code = trim((string) $code);

        // A local number already carrying its own "+" is left alone: someone
        // pasting a full international number should not get the select's code
        // stuck on the front of it.
        if (str_starts_with($local, '+')) {
            return $local;
        }

        if ($code === '' || ! DiallingCodes::isKnown($code)) {
            return $local;
        }

        // A leading zero is a domestic trunk prefix and is dropped when the
        // number is written internationally: German 0171... is +49 171...,
        // never +49 0171...
        $local = ltrim($local, '0');

        if ($local === '') {
            return null;
        }

        return $code.' '.$local;
    }

    /**
     * Take a stored number apart again for redisplay.
     *
     * Codes are tried longest-first so "+201..." is not matched as "+20" with a
     * stray 1 — see DiallingCodes::all().
     *
     * @return array{code: string|null, local: string}
     */
    public static function split(?string $stored): array
    {
        $stored = self::tidy($stored);

        if ($stored === null) {
            return ['code' => null, 'local' => ''];
        }

        foreach (DiallingCodes::all() as $code) {
            if (! str_starts_with($stored, $code)) {
                continue;
            }

            $local = ltrim(substr($stored, strlen($code)));

            // "+201000" splits cleanly, but a bare "+20" with nothing after it
            // is not a number split into halves — it is a code with no number,
            // and pretending otherwise would show an empty input.
            if ($local === '') {
                continue;
            }

            return ['code' => $code, 'local' => $local];
        }

        return ['code' => null, 'local' => $stored];
    }

    /**
     * Collapse whitespace and drop anything that is not part of a number.
     *
     * Customers paste numbers with brackets, dots and non-breaking spaces from
     * their contacts app. Keeping only digits, spaces, "+" and "-" leaves the
     * value readable without letting arbitrary text into a field the admin
     * renders as a `tel:` link.
     */
    private static function tidy(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^\d+\- ]/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}

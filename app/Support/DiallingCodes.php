<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Country;

/**
 * The dialling codes offered beside the phone input on public forms.
 *
 * The `countries` table exists and carries a `phone_code`, but it is seeded
 * with Egypt alone — it describes where the tours *are*, not where guests come
 * from. A booking form for Red Sea tourism that only offers +20 is unusable for
 * the German, Italian and British visitors who make up most of the traffic, so
 * the list here is the fallback the select falls back to when the table has
 * nothing better to offer.
 *
 * Codes are stored on the booking as part of one `customer_phone` string, so
 * nothing downstream — the admin listing, the confirmation email — needs to
 * know this class exists.
 *
 * @see \App\Support\PhoneNumber for how the two halves are joined and split.
 */
final class DiallingCodes
{
    /**
     * The default selection when nothing else is known.
     *
     * Egypt, because the operator is Egyptian and a walk-in or local guest is
     * the one case where the visitor is least likely to know their own code.
     */
    public const DEFAULT = '+20';

    /**
     * Dialling code => English country name.
     *
     * Ordered by how likely a Hurghada/El Gouna guest is to need it — the
     * source markets first, then the rest alphabetically — because a select
     * this long is faster to use when the common answers are at the top.
     *
     * @var array<string, string>
     */
    private const CODES = [
        '+20' => 'Egypt',
        '+49' => 'Germany',
        '+44' => 'United Kingdom',
        '+39' => 'Italy',
        '+7' => 'Russia',
        '+48' => 'Poland',
        '+420' => 'Czechia',
        '+33' => 'France',
        '+31' => 'Netherlands',
        '+32' => 'Belgium',
        '+43' => 'Austria',
        '+41' => 'Switzerland',
        '+36' => 'Hungary',
        '+40' => 'Romania',
        '+421' => 'Slovakia',
        '+380' => 'Ukraine',
        '+46' => 'Sweden',
        '+47' => 'Norway',
        '+45' => 'Denmark',
        '+358' => 'Finland',
        '+372' => 'Estonia',
        '+371' => 'Latvia',
        '+370' => 'Lithuania',
        '+34' => 'Spain',
        '+351' => 'Portugal',
        '+30' => 'Greece',
        '+90' => 'Turkey',
        '+353' => 'Ireland',
        '+352' => 'Luxembourg',
        '+385' => 'Croatia',
        '+386' => 'Slovenia',
        '+359' => 'Bulgaria',
        '+381' => 'Serbia',
        '+1' => 'United States / Canada',
        '+61' => 'Australia',
        '+64' => 'New Zealand',
        '+27' => 'South Africa',
        '+966' => 'Saudi Arabia',
        '+971' => 'United Arab Emirates',
        '+965' => 'Kuwait',
        '+974' => 'Qatar',
        '+973' => 'Bahrain',
        '+968' => 'Oman',
        '+962' => 'Jordan',
        '+961' => 'Lebanon',
        '+964' => 'Iraq',
        '+963' => 'Syria',
        '+970' => 'Palestine',
        '+212' => 'Morocco',
        '+213' => 'Algeria',
        '+216' => 'Tunisia',
        '+218' => 'Libya',
        '+249' => 'Sudan',
        '+86' => 'China',
        '+81' => 'Japan',
        '+82' => 'South Korea',
        '+91' => 'India',
        '+92' => 'Pakistan',
        '+62' => 'Indonesia',
        '+60' => 'Malaysia',
        '+63' => 'Philippines',
        '+66' => 'Thailand',
        '+84' => 'Vietnam',
        '+55' => 'Brazil',
        '+54' => 'Argentina',
        '+52' => 'Mexico',
        '+56' => 'Chile',
        '+57' => 'Colombia',
        '+234' => 'Nigeria',
        '+254' => 'Kenya',
        '+233' => 'Ghana',
        '+251' => 'Ethiopia',
        '+255' => 'Tanzania',
        '+256' => 'Uganda',
    ];

    /**
     * Options for the select, as code => "Country (+code)".
     *
     * Both halves are shown because neither alone is enough: a guest who knows
     * they are German will not recognise a bare "+49", and one who has their
     * old number written down will not recognise a bare "Germany".
     *
     * Any country row carrying a `phone_code` the constant does not know about
     * is merged in, so an admin adding a country gets it in the list without a
     * code change.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::CODES as $code => $name) {
            $options[$code] = $name.' ('.$code.')';
        }

        foreach (self::fromDatabase() as $code => $name) {
            $options[$code] ??= $name.' ('.$code.')';
        }

        return $options;
    }

    /**
     * Whether a submitted code is one this list actually offers.
     *
     * The select is a `<select>`, but nothing stops a client posting whatever
     * it likes — so the value is checked rather than trusted.
     */
    public static function isKnown(string $code): bool
    {
        return array_key_exists($code, self::options());
    }

    /**
     * Every dialling code, longest first.
     *
     * Order matters when splitting a stored number back into its two halves:
     * "+20" is a prefix of "+201", so a shortest-first scan would tear
     * "+20 1000..." apart at the wrong place.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $codes = array_keys(self::options());

        usort($codes, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $codes;
    }

    /**
     * Dialling codes recorded on the countries table.
     *
     * Guarded against the table not existing yet: the public pages render
     * during migration on a fresh install, and a missing table must not take
     * the booking form down with it.
     *
     * @return array<string, string>
     */
    private static function fromDatabase(): array
    {
        try {
            return Country::query()
                ->where('is_active', true)
                ->whereNotNull('phone_code')
                ->orderBy('name')
                ->pluck('name', 'phone_code')
                ->filter(fn (mixed $name, mixed $code): bool => is_string($code) && $code !== '' && is_string($name))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}

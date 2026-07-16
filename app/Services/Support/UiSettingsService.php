<?php

declare(strict_types=1);

namespace App\Services\Support;

use App\Models\Currency;
use App\Models\Language;
use App\Shared\Contracts\SettingRepositoryContract;

/**
 * The site-wide values the public layout renders: company details, the language
 * switcher, and the currency switcher.
 *
 * Every one of these was previously a hardcoded array — a phone number and
 * address that could only be changed by a deploy, and a language list that
 * ignored the `languages` table entirely. They are now read from settings and
 * from the catalogue, both of which the admin can edit.
 */
final class UiSettingsService
{
    public function __construct(
        private readonly SettingRepositoryContract $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function company(): array
    {
        return [
            'name' => $this->settings->get('company_name') ?? config('app.name'),
            'description' => $this->settings->get('company_description') ?? __('website.company_description'),
            'phone' => $this->settings->get('contact_phone'),
            'whatsapp' => $this->settings->get('contact_whatsapp'),
            'email' => $this->settings->get('contact_email'),
            'address' => $this->settings->get('company_address') ?? __('website.company_address'),
            // Only links that are actually set: the previous default of '#'
            // rendered dead social icons on every page.
            'social' => array_filter([
                'facebook' => $this->settings->get('social_facebook'),
                'instagram' => $this->settings->get('social_instagram'),
                'youtube' => $this->settings->get('social_youtube'),
            ]),
        ];
    }

    /**
     * @return list<array{code: string, name: string, native: string, direction: string, flag: string, active: bool}>
     */
    public function activeLanguages(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Language $language): array => [
                'code' => $language->code,
                'name' => $language->name,
                'native' => $language->native_name,
                'direction' => $language->direction,
                'flag' => '',
                'active' => true,
            ])
            ->all();
    }

    /**
     * @return list<array{code: string, name: string, symbol: string, active: bool}>
     */
    public function activeCurrencies(): array
    {
        return Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Currency $currency): array => [
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'active' => true,
            ])
            ->all();
    }

    public function currentCurrency(): string
    {
        $session = session('currency');

        if (is_string($session) && $this->isActiveCurrency($session)) {
            return mb_strtoupper($session);
        }

        // Falls back to the catalogue's default currency rather than a
        // hardcoded 'USD', which could name a currency that is inactive or does
        // not exist.
        return Currency::query()->where('is_default', true)->value('code')
            ?? Currency::query()->where('is_active', true)->orderBy('sort_order')->value('code')
            ?? 'USD';
    }

    public function isActiveCurrency(string $currency): bool
    {
        return collect($this->activeCurrencies())
            ->contains(fn (array $item): bool => $item['code'] === mb_strtoupper($currency));
    }
}

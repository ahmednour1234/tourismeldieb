<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Services\Support\UiSettingsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CurrencySwitcher extends Component
{
    public string $currency = 'USD';

    public function mount(UiSettingsService $settingsService): void
    {
        $this->currency = $settingsService->currentCurrency();
    }

    public function selectCurrency(string $currency, UiSettingsService $settingsService): void
    {
        abort_unless($settingsService->isActiveCurrency($currency), 404);

        session(['currency' => strtoupper($currency)]);
        $this->currency = strtoupper($currency);
    }

    public function render(UiSettingsService $settingsService): View
    {
        return view('livewire.public.currency-switcher', [
            'currencies' => $settingsService->activeCurrencies(),
        ]);
    }
}

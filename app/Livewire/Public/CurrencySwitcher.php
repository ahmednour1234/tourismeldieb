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

    public function selectCurrency(string $currency, UiSettingsService $settingsService): mixed
    {
        abort_unless($settingsService->isActiveCurrency($currency), 404);

        session(['currency' => mb_strtoupper($currency)]);
        $this->currency = mb_strtoupper($currency);

        // Prices are computed server-side per page render, so the current page
        // must reload for the new currency to take effect — updating the session
        // alone left every price unchanged, which is why the switcher looked
        // dead. redirect(request header) reloads wherever the visitor is.
        return $this->redirect(request()->header('Referer', url('/')), navigate: true);
    }

    public function render(UiSettingsService $settingsService): View
    {
        return view('livewire.public.currency-switcher', [
            'currencies' => $settingsService->activeCurrencies(),
        ]);
    }
}

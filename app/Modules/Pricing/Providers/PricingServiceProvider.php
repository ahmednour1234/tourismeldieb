<?php

declare(strict_types=1);

namespace App\Modules\Pricing\Providers;

use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class PricingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Pricing';
    }

    public function moduleDependencies(): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }
}

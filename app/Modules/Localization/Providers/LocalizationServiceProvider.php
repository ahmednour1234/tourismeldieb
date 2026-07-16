<?php

declare(strict_types=1);

namespace App\Modules\Localization\Providers;

use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class LocalizationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Localization';
    }

    public function moduleDependencies(): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\SEO\Providers;

use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class SEOServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'SEO';
    }

    public function moduleDependencies(): array
    {
        return [
            SettingsServiceProvider::class,
            LocalizationServiceProvider::class,
        ];
    }
}

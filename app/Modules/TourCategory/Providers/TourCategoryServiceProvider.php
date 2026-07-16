<?php

declare(strict_types=1);

namespace App\Modules\TourCategory\Providers;

use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class TourCategoryServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'TourCategory';
    }

    public function moduleDependencies(): array
    {
        return [
            LocalizationServiceProvider::class,
        ];
    }
}

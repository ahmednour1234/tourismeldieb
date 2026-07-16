<?php

declare(strict_types=1);

namespace App\Modules\Content\Providers;

use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Modules\SEO\Providers\SEOServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class ContentServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Content';
    }

    public function moduleDependencies(): array
    {
        return [
            LocalizationServiceProvider::class,
            SEOServiceProvider::class,
        ];
    }
}

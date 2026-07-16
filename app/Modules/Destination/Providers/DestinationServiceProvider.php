<?php

declare(strict_types=1);

namespace App\Modules\Destination\Providers;

use App\Modules\Content\Providers\ContentServiceProvider;
use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Modules\SEO\Providers\SEOServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class DestinationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Destination';
    }

    public function moduleDependencies(): array
    {
        return [
            LocalizationServiceProvider::class,
            ContentServiceProvider::class,
            SEOServiceProvider::class,
        ];
    }
}

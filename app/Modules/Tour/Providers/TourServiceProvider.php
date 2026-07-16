<?php

declare(strict_types=1);

namespace App\Modules\Tour\Providers;

use App\Modules\Destination\Providers\DestinationServiceProvider;
use App\Modules\Pricing\Providers\PricingServiceProvider;
use App\Modules\SEO\Providers\SEOServiceProvider;
use App\Modules\TourCategory\Providers\TourCategoryServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class TourServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Tour';
    }

    public function moduleDependencies(): array
    {
        return [
            DestinationServiceProvider::class,
            TourCategoryServiceProvider::class,
            PricingServiceProvider::class,
            SEOServiceProvider::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Availability\Providers;

use App\Modules\Tour\Providers\TourServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class AvailabilityServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Availability';
    }

    public function moduleDependencies(): array
    {
        return [
            TourServiceProvider::class,
        ];
    }
}

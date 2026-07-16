<?php

declare(strict_types=1);

namespace App\Modules\Review\Providers;

use App\Modules\Booking\Providers\BookingServiceProvider;
use App\Modules\Customer\Providers\CustomerServiceProvider;
use App\Modules\Tour\Providers\TourServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class ReviewServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Review';
    }

    public function moduleDependencies(): array
    {
        return [
            BookingServiceProvider::class,
            CustomerServiceProvider::class,
            TourServiceProvider::class,
        ];
    }
}

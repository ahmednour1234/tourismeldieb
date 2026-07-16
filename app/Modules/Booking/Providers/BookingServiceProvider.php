<?php

declare(strict_types=1);

namespace App\Modules\Booking\Providers;

use App\Modules\Availability\Providers\AvailabilityServiceProvider;
use App\Modules\Customer\Providers\CustomerServiceProvider;
use App\Modules\Notification\Providers\NotificationServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Modules\Pricing\Providers\PricingServiceProvider;
use App\Modules\Tour\Providers\TourServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class BookingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Booking';
    }

    public function moduleDependencies(): array
    {
        return [
            TourServiceProvider::class,
            AvailabilityServiceProvider::class,
            PricingServiceProvider::class,
            CustomerServiceProvider::class,
            PaymentServiceProvider::class,
            NotificationServiceProvider::class,
        ];
    }
}

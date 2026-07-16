<?php

declare(strict_types=1);

namespace App\Modules\Payment\Providers;

use App\Modules\Customer\Providers\CustomerServiceProvider;
use App\Modules\Notification\Providers\NotificationServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class PaymentServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Payment';
    }

    public function moduleDependencies(): array
    {
        return [
            CustomerServiceProvider::class,
            NotificationServiceProvider::class,
        ];
    }
}

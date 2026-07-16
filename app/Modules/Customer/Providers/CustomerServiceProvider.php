<?php

declare(strict_types=1);

namespace App\Modules\Customer\Providers;

use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class CustomerServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Customer';
    }

    public function moduleDependencies(): array
    {
        return [
            AuthenticationServiceProvider::class,
        ];
    }
}

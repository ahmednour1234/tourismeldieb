<?php

declare(strict_types=1);

namespace App\Modules\Authorization\Providers;

use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class AuthorizationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Authorization';
    }

    public function moduleDependencies(): array
    {
        return [
            AuthenticationServiceProvider::class,
        ];
    }
}

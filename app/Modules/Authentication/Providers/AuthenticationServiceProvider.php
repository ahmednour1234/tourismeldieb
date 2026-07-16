<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Providers;

use App\Shared\Services\ModuleServiceProvider;

final class AuthenticationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Authentication';
    }
}

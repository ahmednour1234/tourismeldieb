<?php

declare(strict_types=1);

namespace App\Modules\Admin\Providers;

use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Modules\Authorization\Providers\AuthorizationServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Repositories\EloquentResourceRepository;
use App\Shared\Contracts\ResourceRepositoryContract;
use App\Shared\Services\ModuleServiceProvider;

final class AdminServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Admin';
    }

    public function moduleDependencies(): array
    {
        return [
            AuthenticationServiceProvider::class,
            AuthorizationServiceProvider::class,
            SettingsServiceProvider::class,
        ];
    }

    /**
     * The admin module owns every generic resource write, so the one
     * implementation of the contract is bound here.
     *
     * @return array<class-string, class-string>
     */
    public function repositoryBindings(): array
    {
        return [
            ResourceRepositoryContract::class => EloquentResourceRepository::class,
        ];
    }
}

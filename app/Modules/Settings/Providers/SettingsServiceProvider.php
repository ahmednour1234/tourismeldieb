<?php

declare(strict_types=1);

namespace App\Modules\Settings\Providers;

use App\Repositories\EloquentSettingRepository;
use App\Shared\Contracts\SettingRepositoryContract;
use App\Shared\Services\ModuleServiceProvider;

final class SettingsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Settings';
    }

    /**
     * @return array<class-string, class-string>
     */
    public function repositoryBindings(): array
    {
        return [
            SettingRepositoryContract::class => EloquentSettingRepository::class,
        ];
    }
}

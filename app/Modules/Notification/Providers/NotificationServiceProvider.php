<?php

declare(strict_types=1);

namespace App\Modules\Notification\Providers;

use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Shared\Services\ModuleServiceProvider;

final class NotificationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Notification';
    }

    public function moduleDependencies(): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }
}

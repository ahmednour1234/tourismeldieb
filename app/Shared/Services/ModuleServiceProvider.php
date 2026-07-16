<?php

declare(strict_types=1);

namespace App\Shared\Services;

use App\Shared\Contracts\ModuleServiceProviderContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderContract
{
    public function register(): void
    {
        foreach ($this->repositoryBindings() as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }

    public function boot(): void
    {
        $this->loadModuleMigrations();
        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->loadModuleTranslations();
    }

    /**
     * @return list<class-string>
     */
    public function moduleDependencies(): array
    {
        return [];
    }

    /**
     * @return array<class-string, class-string>
     */
    public function repositoryBindings(): array
    {
        return [];
    }

    protected function modulePath(string $path = ''): string
    {
        $basePath = app_path('Modules/'.$this->moduleName());

        return $path === '' ? $basePath : $basePath.DIRECTORY_SEPARATOR.$path;
    }

    private function loadModuleMigrations(): void
    {
        $path = $this->modulePath('Database/Migrations');

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    private function loadModuleRoutes(): void
    {
        $webRoutes = $this->modulePath('Routes/web.php');
        $apiRoutes = $this->modulePath('Routes/api.php');

        if (is_file($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }

        if (is_file($apiRoutes)) {
            Route::middleware('api')->prefix('api')->group($apiRoutes);
        }
    }

    private function loadModuleViews(): void
    {
        $path = $this->modulePath('Resources/views');

        if (is_dir($path)) {
            $this->loadViewsFrom($path, str($this->moduleName())->kebab()->toString());
        }
    }

    private function loadModuleTranslations(): void
    {
        $path = $this->modulePath('Resources/lang');

        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, str($this->moduleName())->kebab()->toString());
            $this->loadJsonTranslationsFrom($path);
        }
    }
}

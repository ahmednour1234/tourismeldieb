<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface ModuleServiceProviderContract
{
    public function moduleName(): string;

    /**
     * @return list<class-string>
     */
    public function moduleDependencies(): array;

    /**
     * @return array<class-string, class-string>
     */
    public function repositoryBindings(): array;
}

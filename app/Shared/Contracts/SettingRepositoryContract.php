<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Owns all database access for settings.
 *
 * Settings are read on every public page render, so reads are cached and every
 * write busts that cache — see the Eloquent implementation.
 */
interface SettingRepositoryContract
{
    /**
     * Every stored setting, keyed by setting key. Values are raw: a scalar, or
     * a locale => value map for translatable keys.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * One setting's value, resolved for the active locale when translatable.
     */
    public function get(string $key, mixed $fallback = null): mixed;

    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void;

    public function flushCache(): void;
}

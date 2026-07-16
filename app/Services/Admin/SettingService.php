<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Admin\SettingSchema;
use App\Shared\Contracts\SettingRepositoryContract;

/**
 * Business rules for settings writes.
 */
final class SettingService
{
    private const LOG_NAME = 'admin';

    public function __construct(
        private readonly SettingRepositoryContract $repository,
    ) {}

    /**
     * Values arrive already validated, keyed by setting key. Translatable keys
     * carry a locale => value map.
     *
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): void
    {
        $changed = $this->changedKeys($values);

        $this->repository->put($values);

        if ($changed === []) {
            return;
        }

        // The values themselves are not logged: they are public marketing
        // details today, but this table is the natural home for API keys later,
        // and an activity log is a poor place to leak one.
        activity(self::LOG_NAME)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties(['keys' => $changed])
            ->log('settings.updated');
    }

    /**
     * @param  array<string, mixed>  $values
     * @return list<string>
     */
    private function changedKeys(array $values): array
    {
        $current = $this->repository->all();

        return collect($values)
            ->filter(fn (mixed $value, string $key): bool => SettingSchema::has($key)
                && ($current[$key] ?? null) !== $value)
            ->keys()
            ->all();
    }
}

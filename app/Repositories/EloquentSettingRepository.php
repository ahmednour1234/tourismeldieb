<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Admin\SettingSchema;
use App\Models\Setting;
use App\Shared\Contracts\SettingRepositoryContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class EloquentSettingRepository implements SettingRepositoryContract
{
    private const CACHE_KEY = 'settings.all';

    /**
     * Every public page reads settings, so the whole table is cached as one
     * entry rather than queried per key. It is small (a handful of rows) and
     * changes rarely; a write busts the entry outright rather than trying to
     * patch it.
     *
     * A read failure degrades to the schema defaults instead of propagating:
     * settings decorate the chrome (footer address, social links), and a
     * marketing page should not 500 because one of them is unreadable. This is
     * the one place that swallows, and it is deliberate — writes still throw,
     * and every other repository read surfaces its errors.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()
                ->pluck('value', 'key')
                ->all());
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    public function get(string $key, mixed $fallback = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        if ($value === null) {
            return $fallback ?? SettingSchema::default($key);
        }

        if (SettingSchema::isTranslatable($key) && is_array($value)) {
            return $this->resolveLocale($value) ?? $fallback ?? SettingSchema::default($key);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                if (! SettingSchema::has($key)) {
                    continue;
                }

                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => SettingSchema::fields()[$key]['group'],
                        'value' => $value,
                        'is_translatable' => SettingSchema::isTranslatable($key),
                        'updated_by' => auth()->id(),
                    ],
                );
            }
        });

        $this->flushCache();
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The active locale's value, falling back to the fallback locale and then
     * to the first non-empty translation — a half-translated setting should
     * still render something rather than a blank footer.
     *
     * @param  array<string, mixed>  $value
     */
    private function resolveLocale(array $value): mixed
    {
        foreach ([app()->getLocale(), (string) config('app.fallback_locale')] as $locale) {
            if (filled($value[$locale] ?? null)) {
                return $value[$locale];
            }
        }

        return collect($value)->first(fn (mixed $item): bool => filled($item));
    }
}

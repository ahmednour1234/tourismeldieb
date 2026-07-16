<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Owns all database access for one admin resource.
 *
 * Per docs/architecture/batch-01.md: repositories own database access;
 * services own business rules; controllers and Blade are presentation only.
 */
interface ResourceRepositoryContract
{
    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(string $resource, ?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function find(string $resource, int|string $id): ?Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function create(string $resource, array $attributes, array $translations = []): Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function update(string $resource, Model $model, array $attributes, array $translations = []): Model;

    public function delete(string $resource, Model $model): void;

    /**
     * Options for a relation select, as id => label in the active locale.
     *
     * @return array<int|string, string>
     */
    public function options(string $resource): array;
}

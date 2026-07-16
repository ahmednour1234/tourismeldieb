<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Admin\ResourceSchema;
use App\DataTransferObjects\ResourceData;
use App\Exceptions\DomainActionException;
use App\Models\Currency;
use App\Models\Language;
use App\Shared\Contracts\ResourceRepositoryContract;
use Illuminate\Database\Eloquent\Model;

/**
 * The business rules for admin resource writes.
 *
 * The repository knows how to persist a row; it does not know that the catalog
 * becomes unusable without a language to render it in, or a currency to price
 * it in. Those rules live here, above persistence and below the controller, so
 * they hold no matter which entry point asks for the write.
 */
final class ResourceService
{
    private const LOG_NAME = 'admin';

    public function __construct(
        private readonly ResourceRepositoryContract $repository,
    ) {}

    public function create(ResourceData $data): Model
    {
        $this->guardResourceExists($data->resource);

        $model = $this->repository->create($data->resource, $data->attributes, $data->translations);

        $this->log($model, 'created', $data->resource);

        return $model;
    }

    public function update(ResourceData $data, Model $model): Model
    {
        $this->guardResourceExists($data->resource);

        $model = $this->repository->update($data->resource, $model, $data->attributes, $data->translations);

        $this->log($model, 'updated', $data->resource);

        return $model;
    }

    public function delete(string $resource, Model $model): void
    {
        $this->guardResourceExists($resource);
        $this->guardLastActiveLanguage($resource, $model);
        $this->guardDefaultCurrency($resource, $model);

        // Logged before the delete: activity() resolves the subject's key at log
        // time, and a force-deleted model no longer has one.
        $this->log($model, 'deleted', $resource);

        $this->repository->delete($resource, $model);
    }

    private function guardResourceExists(string $resource): void
    {
        abort_unless(ResourceSchema::exists($resource), 404);
    }

    /**
     * Without one active language, every public page loses its content.
     */
    private function guardLastActiveLanguage(string $resource, Model $model): void
    {
        if ($resource !== 'languages' || ! $model instanceof Language) {
            return;
        }

        if (! $model->is_active) {
            return;
        }

        $remaining = Language::query()
            ->where('is_active', true)
            ->where('id', '!=', $model->getKey())
            ->exists();

        if (! $remaining) {
            throw DomainActionException::lastActiveLanguage();
        }
    }

    /**
     * Deleting the default currency leaves prices with no currency to resolve.
     */
    private function guardDefaultCurrency(string $resource, Model $model): void
    {
        if ($resource !== 'currencies' || ! $model instanceof Currency) {
            return;
        }

        if ($model->is_default) {
            throw DomainActionException::defaultCurrency();
        }
    }

    private function log(Model $model, string $event, string $resource): void
    {
        activity(self::LOG_NAME)
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->event($event)
            ->withProperties(['resource' => $resource])
            ->log("{$resource}.{$event}");
    }
}

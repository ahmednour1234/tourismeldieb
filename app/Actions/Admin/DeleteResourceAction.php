<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Services\Admin\ResourceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Deletes one admin resource row, soft where the model supports it.
 *
 * The domain guards live in the service and throw before anything is written,
 * so a refused delete leaves the transaction untouched.
 */
final class DeleteResourceAction
{
    public function __construct(
        private readonly ResourceService $service,
    ) {}

    public function __invoke(string $resource, Model $model): void
    {
        DB::transaction(function () use ($resource, $model): void {
            $this->service->delete($resource, $model);
        });
    }
}

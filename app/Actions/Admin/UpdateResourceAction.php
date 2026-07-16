<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DataTransferObjects\ResourceData;
use App\Services\Admin\ResourceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Updates one admin resource row.
 *
 * Owns the outer transaction so column changes, translation upserts and the
 * activity-log entry land as a single unit.
 */
final class UpdateResourceAction
{
    public function __construct(
        private readonly ResourceService $service,
    ) {}

    public function __invoke(ResourceData $data, Model $model): Model
    {
        return DB::transaction(fn (): Model => $this->service->update($data, $model));
    }
}

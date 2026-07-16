<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DataTransferObjects\ResourceData;
use App\Services\Admin\ResourceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Creates one admin resource row.
 *
 * Owns the outer transaction so the write and its activity-log entry commit or
 * roll back together — a log line describing a row that was never inserted is
 * worse than no log line at all.
 */
final class StoreResourceAction
{
    public function __construct(
        private readonly ResourceService $service,
    ) {}

    public function __invoke(ResourceData $data): Model
    {
        return DB::transaction(fn (): Model => $this->service->create($data));
    }
}

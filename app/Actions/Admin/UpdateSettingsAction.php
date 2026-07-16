<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\DB;

/**
 * Saves the settings form.
 *
 * Owns the outer transaction so a partial save cannot leave the site with, say,
 * a new company name but the old address.
 */
final class UpdateSettingsAction
{
    public function __construct(
        private readonly SettingService $service,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public function __invoke(array $values): void
    {
        DB::transaction(fn () => $this->service->update($values));
    }
}

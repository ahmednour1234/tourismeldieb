<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\Admin\AdminDashboardService;
use Illuminate\Contracts\View\View;

final class AdminDashboardController
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        return view('admin.dashboard.index', $this->dashboardService->summary());
    }
}

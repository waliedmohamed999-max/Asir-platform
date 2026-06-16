<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;

class AdminDashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboardService)
    {
        return view('admin.dashboard', $dashboardService->build());
    }
}

<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for displaying the admin dashboard with key metrics and insights.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $metrics = $this->dashboardService->getAdminMetrics();
        return view('admin.dashboard.index', $metrics);
    }
}

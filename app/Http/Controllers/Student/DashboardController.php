<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for displaying the student dashboard with personalized metrics.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $metrics = $this->dashboardService->getStudentMetrics(auth()->user());
        return view('student.dashboard.index', $metrics);
    }
}

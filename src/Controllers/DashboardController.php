<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DashboardService;

/**
 * Controller for the main system dashboard.
 */
class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    /**
     * GET / or GET /dashboard
     */
    public function index(): void
    {
        $stats               = $this->service->getStatistics();
        $recentStudents      = $this->service->getRecentStudents(5);
        $recentEnrollments   = $this->service->getRecentEnrollments(5);
        $departmentBreakdown = $this->service->getDepartmentBreakdown();
        $courseSummary       = $this->service->getCourseSummary(6);

        require __DIR__ . '/../../views/dashboard/index.php';
    }
}

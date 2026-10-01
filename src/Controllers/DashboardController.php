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
        $role = \App\Auth\Auth::getRole();
        $target = match ($role) {
            'student'  => '/my-courses',
            'lecturer' => '/courses',
            default    => '/students',
        };
        header('Location: ' . $target);
        exit;
    }
}

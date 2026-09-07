<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Department;
use App\Services\DepartmentService;
use InvalidArgumentException;

class DepartmentController
{
    private DepartmentService $service;

    public function __construct()
    {
        $this->service = new DepartmentService();
    }

    // GET /departments
    public function index(): void
    {
        $departments = $this->service->getAll();
        require __DIR__ . '/../../views/departments/index.php';
    }

    // GET /departments/create
    public function create(): void
    {
        $errors     = [];
        $department = null;
        require __DIR__ . '/../../views/departments/create.php';
    }

    // POST /departments
    public function store(): void
    {
        $errors = [];
        try {
            $department = $this->service->create($_POST);
            $this->redirect('/departments?success=created');
        } catch (InvalidArgumentException $e) {
            $errors = [$e->getMessage()];
            $department = null;
            require __DIR__ . '/../../views/departments/create.php';
        }
    }

    // GET /departments/{id}/edit
    public function edit(string $id): void
    {
        $department = $this->requireDepartment((int) $id);
        $errors     = [];
        require __DIR__ . '/../../views/departments/create.php';
    }

    // POST /departments/{id}   (_method=PUT)
    public function update(string $id): void
    {
        $department = $this->requireDepartment((int) $id);
        $errors = [];
        try {
            $department = $this->service->update($department, $_POST);
            $this->redirect('/departments?success=updated');
        } catch (InvalidArgumentException $e) {
            $errors = [$e->getMessage()];
            require __DIR__ . '/../../views/departments/create.php';
        }
    }

    // GET /departments/{id}/courses
    public function courses(string $id): void
    {
        $department = $this->requireDepartment((int) $id);
        $courses    = $department->getCourses();
        require __DIR__ . '/../../views/departments/courses.php';
    }

    // GET /departments/{id}/lecturers
    public function lecturers(string $id): void
    {
        $department = $this->requireDepartment((int) $id);
        $lecturers  = $department->getLecturers();
        require __DIR__ . '/../../views/departments/lecturers.php';
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function requireDepartment(int $id): Department
    {
        $department = $this->service->getById($id);
        if (!$department) {
            http_response_code(404);
            exit('<h1>Department not found.</h1>');
        }
        return $department;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

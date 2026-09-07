<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Lecturer;
use App\Models\Department;
use App\Services\LecturerService;
use InvalidArgumentException;

class LecturerController
{
    private LecturerService $service;

    public function __construct()
    {
        $this->service = new LecturerService();
    }

    // GET /lecturers
    public function index(): void
    {
        $lecturers = $this->service->getAll();
        require __DIR__ . '/../../views/lecturers/index.php';
    }

    // GET /lecturers/create
    public function create(): void
    {
        $errors      = [];
        $lecturer    = null;
        $departments = Department::findAll();
        require __DIR__ . '/../../views/lecturers/create.php';
    }

    // POST /lecturers
    public function store(): void
    {
        $errors = [];
        try {
            $this->service->register($_POST);
            $this->redirect('/lecturers?success=registered');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $lecturer    = null;
            $departments = Department::findAll();
            require __DIR__ . '/../../views/lecturers/create.php';
        }
    }

    // GET /lecturers/{id}/edit
    public function edit(string $id): void
    {
        $lecturer    = $this->requireLecturer((int) $id);
        $errors      = [];
        $departments = Department::findAll();
        require __DIR__ . '/../../views/lecturers/create.php';
    }

    // POST /lecturers/{id}  (_method=PUT)
    public function update(string $id): void
    {
        $lecturer = $this->requireLecturer((int) $id);
        $errors   = [];
        try {
            $this->service->update($lecturer, $_POST);
            $this->redirect('/lecturers?success=updated');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $departments = Department::findAll();
            require __DIR__ . '/../../views/lecturers/create.php';
        }
    }

    // GET /lecturers/{id}/courses
    public function courses(string $id): void
    {
        $lecturer = $this->requireLecturer((int) $id);
        $courses  = $lecturer->getCourses();
        require __DIR__ . '/../../views/lecturers/courses.php';
    }

    // POST /lecturers/{id}/associate-department
    public function associateDepartment(string $id): void
    {
        header('Content-Type: application/json');
        try {
            $lecturer     = $this->requireLecturer((int) $id);
            $departmentId = $_POST['department_id'] !== '' ? (int) $_POST['department_id'] : null;
            $this->service->associateDepartment($lecturer, $departmentId);
            echo json_encode(['success' => true, 'message' => 'Department association updated.']);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function requireLecturer(int $id): Lecturer
    {
        $lecturer = $this->service->getById($id);
        if (!$lecturer) {
            http_response_code(404);
            exit('<h1>Lecturer not found.</h1>');
        }
        return $lecturer;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

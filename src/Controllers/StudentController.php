<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Student;
use App\Models\Department;
use App\Services\StudentService;
use InvalidArgumentException;

class StudentController
{
    private StudentService $service;

    public function __construct()
    {
        $this->service = new StudentService();
    }

    // GET /students[?query=XX]
    public function index(): void
    {
        $searchQuery = isset($_GET['query']) ? trim($_GET['query']) : (isset($_GET['search']) ? trim($_GET['search']) : '');
        $students    = $searchQuery !== '' ? $this->service->search($searchQuery) : $this->service->getAll();
        $departments = Department::findAll();
        require __DIR__ . '/../../views/students/index.php';
    }

    // GET /students/create
    public function create(): void
    {
        $errors      = [];
        $student     = null;
        $address     = null;
        $departments = Department::findAll();
        require __DIR__ . '/../../views/students/create.php';
    }

    // POST /students
    public function store(): void
    {
        $errors = [];
        try {
            $student = $this->service->registerStudent($_POST);
            $this->redirect('/students?success=created');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $student     = null;
            $address     = null;
            $departments = Department::findAll();
            require __DIR__ . '/../../views/students/create.php';
        }
    }

    // GET /students/{id}
    public function show(string $id): void
    {
        $student     = $this->requireStudent((int) $id);
        $address     = $student->getAddress();
        $department  = $student->getDepartment();
        $enrollments = $student->getEnrollments();
        $courses     = $student->getCourses();
        require __DIR__ . '/../../views/students/show.php';
    }

    // GET /students/{id}/edit
    public function edit(string $id): void
    {
        $student     = $this->requireStudent((int) $id);
        $address     = $student->getAddress();
        $errors      = [];
        $departments = Department::findAll();
        require __DIR__ . '/../../views/students/edit.php';
    }

    // POST /students/{id}
    public function update(string $id): void
    {
        $student = $this->requireStudent((int) $id);
        $errors  = [];
        try {
            $this->service->updateStudent($student, $_POST);
            $this->redirect('/students/' . $student->id . '?success=updated');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $address     = $student->getAddress();
            $departments = Department::findAll();
            require __DIR__ . '/../../views/students/edit.php';
        }
    }

    // GET or POST /profile
    public function profile(): void
    {
        $studentId = \App\Auth\Auth::getStudentId() ?? 1;
        $student   = $this->requireStudent($studentId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->service->updateStudent($student, $_POST);
                $this->redirect('/profile?success=updated');
            } catch (\Exception $e) {
                $errors      = [$e->getMessage()];
                $address     = $student->getAddress();
                $departments = Department::findAll();
                require __DIR__ . '/../../views/students/edit.php';
                return;
            }
        }

        $address     = $student->getAddress();
        $enrollments = $student->getEnrollments();
        require __DIR__ . '/../../views/students/show.php';
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function requireStudent(int $id): Student
    {
        $student = $this->service->getById($id);
        if (!$student) {
            http_response_code(404);
            exit('<h1>Student not found.</h1>');
        }
        return $student;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

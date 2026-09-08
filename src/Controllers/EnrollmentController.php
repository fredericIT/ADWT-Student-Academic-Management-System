<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentService;
use InvalidArgumentException;

class EnrollmentController
{
    private EnrollmentService $service;

    public function __construct()
    {
        $this->service = new EnrollmentService();
    }

    // GET /enrollments[?student_id=XX]
    public function index(): void
    {
        $selectedStudentId = isset($_GET['student_id']) && $_GET['student_id'] !== ''
            ? (int) $_GET['student_id']
            : null;

        $selectedCourseId = isset($_GET['course_id']) && $_GET['course_id'] !== ''
            ? (int) $_GET['course_id']
            : null;

        if ($selectedStudentId !== null) {
            $enrollments = Enrollment::findByStudent($selectedStudentId);
        } elseif ($selectedCourseId !== null) {
            $enrollments = Enrollment::findByCourse($selectedCourseId, false);
        } else {
            $enrollments = $this->service->getAll();
        }

        $students = Student::findAll();
        $courses  = Course::findAll();

        require __DIR__ . '/../../views/enrollments/index.php';
    }

    // GET /enrollments/create
    public function create(): void
    {
        $errors            = [];
        $students          = Student::findAll();
        $courses           = Course::findAll();
        $selectedStudentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;
        $selectedCourseId  = isset($_GET['course_id'])  ? (int) $_GET['course_id']  : null;

        require __DIR__ . '/../../views/enrollments/create.php';
    }

    // POST /enrollments
    public function store(): void
    {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $courseId  = (int) ($_POST['course_id'] ?? 0);

        try {
            $this->service->enroll($studentId, $courseId);
            $this->redirect('/enrollments?success=enrolled');
        } catch (InvalidArgumentException $e) {
            $errors            = [$e->getMessage()];
            $students          = Student::findAll();
            $courses           = Course::findAll();
            $selectedStudentId = $studentId ?: null;
            $selectedCourseId  = $courseId  ?: null;

            require __DIR__ . '/../../views/enrollments/create.php';
        }
    }

    // POST /enrollments/{id}/drop
    public function drop(string $id): void
    {
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $this->service->drop((int) $id);
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Course dropped successfully.']);
                return;
            }
            $this->redirect('/enrollments?success=dropped');
        } catch (InvalidArgumentException $e) {
            if ($isJson) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
            $this->redirect('/enrollments?error=' . urlencode($e->getMessage()));
        }
    }

    // GET /courses/{id}/students
    public function courseStudents(string $id): void
    {
        $courseId = (int) $id;
        $course   = Course::findById($courseId);
        if (!$course) {
            http_response_code(404);
            exit('<h1>Course not found.</h1>');
        }

        $students    = $this->service->getCourseStudents($courseId);
        $enrollments = Enrollment::findByCourse($courseId, false);

        require __DIR__ . '/../../views/courses/students.php';
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

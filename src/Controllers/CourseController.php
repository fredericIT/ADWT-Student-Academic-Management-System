<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Lecturer;
use App\Services\CourseService;
use InvalidArgumentException;

class CourseController
{
    private CourseService $service;

    public function __construct()
    {
        $this->service = new CourseService();
    }

    // GET /courses[?code=XX]
    public function index(): void
    {
        $searchCode = isset($_GET['code']) ? trim($_GET['code']) : '';
        $searchResult = null;

        if ($searchCode !== '') {
            $searchResult = $this->service->searchByCode($searchCode);
        }

        $courses     = $this->service->getAll();
        $departments = Department::findAll();

        $isStudent  = \App\Auth\Auth::isStudent();
        $isLecturer = \App\Auth\Auth::isLecturer();
        $isAdmin    = \App\Auth\Auth::isAdmin();

        $enrolledCourseIds = [];
        if ($isStudent) {
            $studentId = \App\Auth\Auth::getStudentId() ?? 1;
            $student   = \App\Models\Student::findById($studentId) ?? (\App\Models\Student::findAll()[0] ?? null);
            if ($student) {
                foreach ($student->getCourses() as $c) {
                    $enrolledCourseIds[$c->id] = true;
                }
            }
        }

        $assignedCourseIds = [];
        if ($isLecturer) {
            $lecturerId = \App\Auth\Auth::getLecturerId() ?? 1;
            $lecturer   = \App\Models\Lecturer::findById($lecturerId) ?? (\App\Models\Lecturer::findAll()[0] ?? null);
            if ($lecturer) {
                foreach ($lecturer->getCourses() as $c) {
                    $assignedCourseIds[$c->id] = true;
                }
            }
        }

        require __DIR__ . '/../../views/courses/index.php';
    }

    // GET /courses/create
    public function create(): void
    {
        $errors      = [];
        $course      = null;
        $departments = Department::findAll();
        $lecturers   = Lecturer::findAll();
        require __DIR__ . '/../../views/courses/create.php';
    }

    // POST /courses
    public function store(): void
    {
        $errors = [];
        try {
            $this->service->create($_POST);
            $this->redirect('/courses?success=created');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $course      = null;
            $departments = Department::findAll();
            $lecturers   = Lecturer::findAll();
            require __DIR__ . '/../../views/courses/create.php';
        }
    }

    // GET /courses/{id}/edit
    public function edit(string $id): void
    {
        $course      = $this->requireCourse((int) $id);
        $errors      = [];
        $departments = Department::findAll();
        $lecturers   = Lecturer::findAll();
        require __DIR__ . '/../../views/courses/create.php';
    }

    // POST /courses/{id}   (_method=PUT)
    public function update(string $id): void
    {
        $course = $this->requireCourse((int) $id);
        $errors = [];
        try {
            $this->service->update($course, $_POST);
            $this->redirect('/courses?success=updated');
        } catch (InvalidArgumentException $e) {
            $errors      = [$e->getMessage()];
            $departments = Department::findAll();
            $lecturers   = Lecturer::findAll();
            require __DIR__ . '/../../views/courses/create.php';
        }
    }

    // POST /courses/{id}/assign-lecturer
    public function assignLecturer(string $id): void
    {
        $this->setJsonHeaders();
        try {
            $lecturerId = (int) ($_POST['lecturer_id'] ?? 0);
            $this->service->assignLecturer((int) $id, $lecturerId);
            echo json_encode(['success' => true, 'message' => 'Lecturer assigned successfully.']);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // POST /courses/{id}/remove-lecturer  (_method=DELETE)
    public function removeLecturer(string $id): void
    {
        $this->setJsonHeaders();
        try {
            $lecturerId = (int) ($_POST['lecturer_id'] ?? 0);
            $this->service->removeLecturer((int) $id, $lecturerId);
            echo json_encode(['success' => true, 'message' => 'Lecturer removed.']);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // POST /courses/{id}/delete
    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $course = $this->requireCourse((int) $id);
        $this->service->delete($course);
        $this->redirect('/courses?success=deleted');
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function requireCourse(int $id): Course
    {
        $course = $this->service->getById($id);
        if (!$course) {
            http_response_code(404);
            exit('<h1>Course not found.</h1>');
        }
        return $course;
    }

    private function requireAdmin(): void
    {
        if (!\App\Auth\Auth::isAdmin()) {
            http_response_code(403);
            exit('<h1>Access denied.</h1>');
        }
    }

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

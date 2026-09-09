<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AcademicService;
use InvalidArgumentException;

/**
 * Controller handling Academic Results, Mark Recording, Transcripts, and Grade Sheets.
 */
class ResultController
{
    private AcademicService $service;

    public function __construct()
    {
        $this->service = new AcademicService();
    }

    // GET /results[?student_id=XX&course_id=YY]
    public function index(): void
    {
        $role            = \App\Auth\Auth::getRole();
        $isStudent       = \App\Auth\Auth::isStudent();
        $isLecturer      = \App\Auth\Auth::isLecturer();
        $currentStudent  = null;
        $currentLecturer = null;
        $studentRecord    = null;

        if ($isStudent) {
            $studentId      = \App\Auth\Auth::getStudentId() ?? 1;
            $currentStudent = Student::findById($studentId) ?? (Student::findAll()[0] ?? null);
            if ($currentStudent) {
                $studentId     = $currentStudent->id;
                $studentRecord = $this->service->getStudentResults($studentId);
                $grades        = $studentRecord->getGrades();
            } else {
                $grades = [];
            }
            $stats             = [];
            $students          = [];
            $courses           = [];
            $lecturers         = [];
            $selectedStudentId = $currentStudent ? $currentStudent->id : null;
            $selectedCourseId  = null;
        } elseif ($isLecturer) {
            $lecturerId        = \App\Auth\Auth::getLecturerId() ?? 1;
            $currentLecturer   = Lecturer::findById($lecturerId) ?? (Lecturer::findAll()[0] ?? null);
            $assignedCourses   = $currentLecturer ? $currentLecturer->getCourses() : [];
            $assignedCourseIds = array_map(fn($c) => $c->id, $assignedCourses);

            $selectedCourseId  = isset($_GET['course_id']) && $_GET['course_id'] !== ''
                ? (int) $_GET['course_id']
                : null;

            if ($selectedCourseId !== null && in_array($selectedCourseId, $assignedCourseIds, true)) {
                $grades = $this->service->getCourseGrades($selectedCourseId);
            } else {
                $allGrades = $this->service->getAllGrades();
                $grades    = array_values(array_filter($allGrades, fn(Grade $g) => in_array($g->courseId, $assignedCourseIds, true)));
            }

            $total    = count($grades);
            $passed   = count(array_filter($grades, fn(Grade $g) => $g->isPass()));
            $sum      = array_reduce($grades, fn($acc, Grade $g) => $acc + $g->mark, 0.0);
            $avg      = $total > 0 ? round($sum / $total, 2) : 0.0;
            $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0.0;

            $stats = [
                'total_grades'   => $total,
                'passed_count'   => $passed,
                'failed_count'   => $total - $passed,
                'average_mark'   => $avg,
                'pass_rate'      => $passRate,
                'assigned_count' => count($assignedCourses),
            ];

            $students          = Student::findAll();
            $courses           = $assignedCourses;
            $lecturers         = $currentLecturer ? [$currentLecturer] : [];
            $selectedStudentId = null;
        } else {
            // Administrator: complete institutional oversight
            $selectedStudentId = isset($_GET['student_id']) && $_GET['student_id'] !== ''
                ? (int) $_GET['student_id']
                : null;

            $selectedCourseId = isset($_GET['course_id']) && $_GET['course_id'] !== ''
                ? (int) $_GET['course_id']
                : null;

            if ($selectedStudentId !== null) {
                $grades = $this->service->getStudentGrades($selectedStudentId);
            } elseif ($selectedCourseId !== null) {
                $grades = $this->service->getCourseGrades($selectedCourseId);
            } else {
                $grades = $this->service->getAllGrades();
            }

            $stats     = $this->service->getStatistics();
            $students  = Student::findAll();
            $courses   = Course::findAll();
            $lecturers = Lecturer::findAll();
        }

        require __DIR__ . '/../../views/results/index.php';
    }

    // GET /results/record[?course_id=XX&student_id=YY&lecturer_id=ZZ]
    public function record(): void
    {
        $isLecturer      = \App\Auth\Auth::isLecturer();
        $currentLecturer = null;

        if ($isLecturer) {
            $lecturerId         = \App\Auth\Auth::getLecturerId() ?? 1;
            $currentLecturer    = Lecturer::findById($lecturerId) ?? (Lecturer::findAll()[0] ?? null);
            $selectedLecturerId = $currentLecturer ? $currentLecturer->id : $lecturerId;
            $courses            = $currentLecturer ? $currentLecturer->getCourses() : [];
        } else {
            $selectedLecturerId = isset($_GET['lecturer_id']) && $_GET['lecturer_id'] !== '' ? (int) $_GET['lecturer_id'] : null;
            $courses            = Course::findAll();
        }

        $errors            = [];
        $selectedCourseId  = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int) $_GET['course_id'] : null;
        $selectedStudentId = isset($_GET['student_id']) && $_GET['student_id'] !== '' ? (int) $_GET['student_id'] : null;
        $lecturers         = $isLecturer && $currentLecturer ? [$currentLecturer] : Lecturer::findAll();

        $enrolledStudents = [];
        if ($selectedCourseId !== null) {
            $course = Course::findById($selectedCourseId);
            if ($course) {
                $enrolledStudents = $course->getEnrolledStudents();
            }
        }

        require __DIR__ . '/../../views/results/record.php';
    }

    // POST /results/record
    public function store(): void
    {
        $isLecturer      = \App\Auth\Auth::isLecturer();
        $currentLecturer = null;

        if ($isLecturer) {
            $lecturerId      = \App\Auth\Auth::getLecturerId() ?? 1;
            $currentLecturer = Lecturer::findById($lecturerId) ?? (Lecturer::findAll()[0] ?? null);
            $lecturerId      = $currentLecturer ? $currentLecturer->id : $lecturerId;
            $courses         = $currentLecturer ? $currentLecturer->getCourses() : [];
        } else {
            $lecturerId = !empty($_POST['lecturer_id']) ? (int) $_POST['lecturer_id'] : null;
            $courses    = Course::findAll();
        }

        $studentId  = (int) ($_POST['student_id'] ?? 0);
        $courseId   = (int) ($_POST['course_id'] ?? 0);
        $markInput  = trim((string) ($_POST['mark'] ?? ''));
        $remarks    = !empty($_POST['remarks']) ? trim((string) $_POST['remarks']) : null;

        if ($markInput === '' || !is_numeric($markInput)) {
            $errors             = ['Invalid mark. Mark must be a valid number between 0 and 100.'];
            $selectedCourseId   = $courseId ?: null;
            $selectedStudentId  = $studentId ?: null;
            $selectedLecturerId = $lecturerId ?: null;
            $lecturers          = $isLecturer && $currentLecturer ? [$currentLecturer] : Lecturer::findAll();
            $enrolledStudents   = $courseId ? (Course::findById($courseId)?->getEnrolledStudents() ?? []) : [];
            require __DIR__ . '/../../views/results/record.php';
            return;
        }

        $mark = (float) $markInput;

        try {
            $this->service->recordMark($studentId, $courseId, $mark, $lecturerId, $remarks);
            $this->redirect('/results?success=grade_recorded' . ($courseId ? '&course_id=' . $courseId : ''));
        } catch (InvalidArgumentException $e) {
            $errors             = [$e->getMessage()];
            $selectedCourseId   = $courseId ?: null;
            $selectedStudentId  = $studentId ?: null;
            $selectedLecturerId = $lecturerId ?: null;
            $lecturers          = $isLecturer && $currentLecturer ? [$currentLecturer] : Lecturer::findAll();
            $enrolledStudents   = $courseId ? (Course::findById($courseId)?->getEnrolledStudents() ?? []) : [];
            require __DIR__ . '/../../views/results/record.php';
        }
    }

    // GET /results/{id}/edit
    public function edit(string $id): void
    {
        $grade = $this->requireGrade((int) $id);
        $errors    = [];
        $lecturers = Lecturer::findAll();

        require __DIR__ . '/../../views/results/edit.php';
    }

    // POST /results/{id}
    public function update(string $id): void
    {
        $grade = $this->requireGrade((int) $id);
        $markInput  = trim((string) ($_POST['mark'] ?? ''));
        $lecturerId = !empty($_POST['lecturer_id']) ? (int) $_POST['lecturer_id'] : null;
        $remarks    = !empty($_POST['remarks']) ? trim((string) $_POST['remarks']) : null;

        if ($markInput === '' || !is_numeric($markInput)) {
            $errors    = ['Invalid mark. Mark must be a valid number between 0 and 100.'];
            $lecturers = Lecturer::findAll();
            require __DIR__ . '/../../views/results/edit.php';
            return;
        }

        $mark = (float) $markInput;

        try {
            $this->service->updateMark($grade->id, $mark, $lecturerId, $remarks);
            $this->redirect('/results?success=grade_updated');
        } catch (InvalidArgumentException $e) {
            $errors    = [$e->getMessage()];
            $lecturers = Lecturer::findAll();
            require __DIR__ . '/../../views/results/edit.php';
        }
    }

    // GET /students/{id}/results
    public function studentResults(string $id): void
    {
        $studentId = (int) $id;
        $student   = Student::findById($studentId);
        if (!$student) {
            http_response_code(404);
            exit('<h1>Student not found.</h1>');
        }

        $record  = $this->service->getOrCreateAcademicRecord($studentId);
        $grades  = $record->getGrades();
        $average = $record->calculateAverage();
        $weighted= $record->calculateWeightedAverage();
        $status  = $record->getOverallStatus();

        require __DIR__ . '/../../views/results/student.php';
    }

    // GET /courses/{id}/results
    public function courseResults(string $id): void
    {
        $courseId = (int) $id;
        $course   = Course::findById($courseId);
        if (!$course) {
            http_response_code(404);
            exit('<h1>Course not found.</h1>');
        }

        $grades     = $this->service->getCourseGrades($courseId);
        $lecturers  = $course->getLecturers();
        $enrollments= $course->getEnrollments(true);

        $marks = array_map(fn(Grade $g) => $g->mark, $grades);
        $courseAvg = count($marks) > 0 ? round(array_sum($marks) / count($marks), 2) : 0.0;
        $passCount = count(array_filter($grades, fn(Grade $g) => $g->isPass()));
        $passRate  = count($grades) > 0 ? round(($passCount / count($grades)) * 100, 1) : 0.0;

        require __DIR__ . '/../../views/results/course.php';
    }

    // POST /results/{id}/delete
    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $grade = $this->requireGrade((int) $id);
        $this->service->deleteGrade($grade);
        $this->redirect('/results?success=deleted');
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function requireGrade(int $id): Grade
    {
        $grade = $this->service->getGradeById($id);
        if (!$grade) {
            http_response_code(404);
            exit('<h1>Grade record not found.</h1>');
        }
        return $grade;
    }

    private function requireAdmin(): void
    {
        if (!\App\Auth\Auth::isAdmin()) {
            http_response_code(403);
            exit('<h1>Access denied.</h1>');
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

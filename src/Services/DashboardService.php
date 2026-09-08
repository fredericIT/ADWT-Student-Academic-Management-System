<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Student;
use PDO;

/**
 * Service providing aggregated academic statistics, metrics,
 * and recent activity feeds for the system dashboard.
 */
class DashboardService
{
    /**
     * Get system-wide KPI metrics and counts.
     *
     * @return array{
     *     total_students: int,
     *     total_courses: int,
     *     total_departments: int,
     *     total_lecturers: int,
     *     total_enrollments: int,
     *     active_enrollments: int,
     *     dropped_enrollments: int
     * }
     */
    public function getStatistics(): array
    {
        $pdo = Connection::getInstance();

        $countOf = function (string $table, ?string $where = null, array $params = []) use ($pdo): int {
            $sql = "SELECT COUNT(*) FROM {$table}";
            if ($where !== null) {
                $sql .= " WHERE {$where}";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        };

        return [
            'total_students'      => $countOf('students'),
            'total_courses'       => $countOf('courses'),
            'total_departments'   => $countOf('departments'),
            'total_lecturers'     => $countOf('lecturers'),
            'total_enrollments'   => $countOf('enrollments'),
            'active_enrollments'  => $countOf('enrollments', 'status = :st', [':st' => Enrollment::STATUS_ACTIVE]),
            'dropped_enrollments' => $countOf('enrollments', 'status = :st', [':st' => Enrollment::STATUS_DROPPED]),
        ];
    }

    /**
     * Retrieve the most recently registered students.
     *
     * @return Student[]
     */
    public function getRecentStudents(int $limit = 5): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM students ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn(array $row) => Student::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Retrieve the most recent course enrollment events with related student and course details.
     *
     * @return array<array{
     *     enrollment: Enrollment,
     *     student: ?Student,
     *     course: ?Course
     * }>
     */
    public function getRecentEnrollments(int $limit = 5): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM enrollments ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $results = [];

        foreach ($rows as $row) {
            $enrollment = Enrollment::fromRow($row);
            $results[] = [
                'enrollment' => $enrollment,
                'student'    => $enrollment->getStudent(),
                'course'     => $enrollment->getCourse(),
            ];
        }

        return $results;
    }

    /**
     * Retrieve academic departments with course and lecturer totals.
     *
     * @return array<array{
     *     department: Department,
     *     course_count: int,
     *     lecturer_count: int
     * }>
     */
    public function getDepartmentBreakdown(): array
    {
        $departments = Department::findAll();
        $breakdown = [];

        foreach ($departments as $dept) {
            $breakdown[] = [
                'department'     => $dept,
                'course_count'   => count($dept->getCourses()),
                'lecturer_count' => count($dept->getLecturers()),
            ];
        }

        return $breakdown;
    }

    /**
     * Retrieve top courses and their active enrollment counts.
     *
     * @return array<array{
     *     course: Course,
     *     department: ?Department,
     *     enrolled_count: int
     * }>
     */
    public function getCourseSummary(int $limit = 6): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM courses ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $courses = array_map(fn(array $row) => Course::fromRow($row), $stmt->fetchAll());
        $summary = [];

        foreach ($courses as $course) {
            $enrolled = count($course->getEnrolledStudents());
            $summary[] = [
                'course'         => $course,
                'department'     => $course->getDepartment(),
                'enrolled_count' => $enrolled,
            ];
        }

        return $summary;
    }
}

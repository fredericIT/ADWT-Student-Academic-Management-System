<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Student;
use PDO;

/**
 * Service handling global academic search capabilities:
 * 1. Search student by ID
 * 2. Search student by name
 * 3. Search course by course code
 * 4. Find students registered for a course
 * 5. Unified global multi-entity search
 */
class SearchService
{
    /**
     * 1. Search student by Registration ID (exact or partial match).
     *
     * @return Student[]
     */
    public function searchStudentsById(string $studentId): array
    {
        $term = trim($studentId);
        if ($term === '') {
            return [];
        }

        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM students
              WHERE UPPER(student_id) LIKE UPPER(:term)
              ORDER BY student_id'
        );
        $stmt->execute([':term' => '%' . $term . '%']);

        return array_map(fn(array $row) => Student::fromRow($row), $stmt->fetchAll());
    }

    /**
     * 2. Search student by name (first name, last name, or combined full name).
     *
     * @return Student[]
     */
    public function searchStudentsByName(string $name): array
    {
        $term = trim($name);
        if ($term === '') {
            return [];
        }

        $pdo = Connection::getInstance();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $concatExpr = $driver === 'sqlite'
            ? "(first_name || ' ' || last_name)"
            : "CONCAT(first_name, ' ', last_name)";

        $stmt = $pdo->prepare(
            "SELECT * FROM students
              WHERE first_name LIKE :t1
                 OR last_name  LIKE :t2
                 OR {$concatExpr} LIKE :t3
              ORDER BY last_name, first_name"
        );
        $like = '%' . $term . '%';
        $stmt->execute([':t1' => $like, ':t2' => $like, ':t3' => $like]);

        return array_map(fn(array $row) => Student::fromRow($row), $stmt->fetchAll());
    }

    /**
     * 3. Search course by course code (case-insensitive, exact or partial).
     *
     * @return Course[]
     */
    public function searchCoursesByCode(string $code): array
    {
        $term = trim($code);
        if ($term === '') {
            return [];
        }

        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM courses
              WHERE UPPER(code) LIKE UPPER(:term)
              ORDER BY code'
        );
        $stmt->execute([':term' => '%' . $term . '%']);

        return array_map(fn(array $row) => Course::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Search courses by code or course name.
     *
     * @return Course[]
     */
    public function searchCourses(string $query): array
    {
        $term = trim($query);
        if ($term === '') {
            return [];
        }

        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM courses
              WHERE UPPER(code) LIKE UPPER(:t1)
                 OR name LIKE :t2
              ORDER BY code'
        );
        $like = '%' . $term . '%';
        $stmt->execute([':t1' => $like, ':t2' => $like]);

        return array_map(fn(array $row) => Course::fromRow($row), $stmt->fetchAll());
    }

    /**
     * 4. Find students actively registered for a particular course.
     *
     * @param int|string $courseIdOrCode
     * @return array{course: ?Course, students: Student[]}
     */
    public function getStudentsByCourse(int|string $courseIdOrCode): array
    {
        $course = null;
        if (is_numeric($courseIdOrCode)) {
            $course = Course::findById((int) $courseIdOrCode);
        } else {
            $course = Course::findByCode((string) $courseIdOrCode);
        }

        if (!$course) {
            return ['course' => null, 'students' => []];
        }

        return [
            'course'   => $course,
            'students' => $course->getEnrolledStudents(),
        ];
    }

    /**
     * Unified global search across students and courses based on a term and filter.
     *
     * @param string $query
     * @param string $type One of 'all', 'student_id', 'student_name', 'course_code', 'course'
     * @return array{
     *     students: Student[],
     *     courses: Course[],
     *     total_results: int
     * }
     */
    public function globalSearch(string $query, string $type = 'all'): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'students'      => [],
                'courses'       => [],
                'total_results' => 0,
            ];
        }

        $students = [];
        $courses  = [];

        switch ($type) {
            case 'student_id':
                $students = $this->searchStudentsById($query);
                break;

            case 'student_name':
                $students = $this->searchStudentsByName($query);
                break;

            case 'course_code':
                $courses = $this->searchCoursesByCode($query);
                break;

            case 'course':
                $courses = $this->searchCourses($query);
                break;

            case 'all':
            default:
                // Search students by ID and Name
                $studentsById   = $this->searchStudentsById($query);
                $studentsByName = $this->searchStudentsByName($query);

                // Merge and deduplicate students
                $mergedStudents = [];
                foreach (array_merge($studentsById, $studentsByName) as $st) {
                    $mergedStudents[$st->id] = $st;
                }
                $students = array_values($mergedStudents);

                // Search courses by code or name
                $courses = $this->searchCourses($query);
                break;
        }

        return [
            'students'      => $students,
            'courses'       => $courses,
            'total_results' => count($students) + count($courses),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\DashboardService;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for DashboardService and academic summary statistics (Issue #7).
 */
class DashboardTest extends TestCase
{
    private static PDO $pdo;
    private DashboardService $service;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::$pdo->exec('
            CREATE TABLE departments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                code TEXT NOT NULL UNIQUE,
                description TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE lecturers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                code TEXT NOT NULL UNIQUE,
                description TEXT NULL,
                credits INTEGER NOT NULL DEFAULT 1,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE course_lecturer (
                course_id INTEGER NOT NULL REFERENCES courses(id),
                lecturer_id INTEGER NOT NULL REFERENCES lecturers(id),
                assigned_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (course_id, lecturer_id)
            );
            CREATE TABLE students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id TEXT NOT NULL UNIQUE,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE enrollments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL REFERENCES students(id),
                course_id INTEGER NOT NULL REFERENCES courses(id),
                status TEXT NOT NULL DEFAULT "active",
                enrollment_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec('DELETE FROM enrollments; DELETE FROM course_lecturer; DELETE FROM courses; DELETE FROM lecturers; DELETE FROM students; DELETE FROM departments;');
        $this->service = new DashboardService();
    }

    public function test_dashboard_statistics_calculation(): void
    {
        $dept = Department::create(['name' => 'Computer Science', 'code' => 'CS']);
        $lecturer = Lecturer::create(['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@uni.ac', 'department_id' => $dept->id]);
        $course = Course::create(['name' => 'Programming 101', 'code' => 'CS101', 'credits' => 3, 'department_id' => $dept->id]);

        $s1 = Student::create(['student_id' => 'STU01', 'first_name' => 'Alice', 'last_name' => 'A', 'email' => 'alice@uni.ac']);
        $s2 = Student::create(['student_id' => 'STU02', 'first_name' => 'Bob', 'last_name' => 'B', 'email' => 'bob@uni.ac']);

        Enrollment::create(['student_id' => $s1->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_ACTIVE]);
        Enrollment::create(['student_id' => $s2->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_DROPPED]);

        $stats = $this->service->getStatistics();

        $this->assertSame(2, $stats['total_students']);
        $this->assertSame(1, $stats['total_courses']);
        $this->assertSame(1, $stats['total_departments']);
        $this->assertSame(1, $stats['total_lecturers']);
        $this->assertSame(2, $stats['total_enrollments']);
        $this->assertSame(1, $stats['active_enrollments']);
        $this->assertSame(1, $stats['dropped_enrollments']);
    }

    public function test_recent_students_feed(): void
    {
        Student::create(['student_id' => 'STU1', 'first_name' => 'S1', 'last_name' => 'L1', 'email' => 's1@uni.ac']);
        Student::create(['student_id' => 'STU2', 'first_name' => 'S2', 'last_name' => 'L2', 'email' => 's2@uni.ac']);

        $recent = $this->service->getRecentStudents(5);
        $this->assertCount(2, $recent);
        $this->assertSame('STU2', $recent[0]->studentId); // most recent first
    }

    public function test_recent_enrollments_feed(): void
    {
        $course = Course::create(['name' => 'Math', 'code' => 'MA101', 'credits' => 3]);
        $s1 = Student::create(['student_id' => 'STU1', 'first_name' => 'S1', 'last_name' => 'L1', 'email' => 's1@uni.ac']);

        Enrollment::create(['student_id' => $s1->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_ACTIVE]);

        $recent = $this->service->getRecentEnrollments(5);
        $this->assertCount(1, $recent);
        $this->assertSame('STU1', $recent[0]['student']->studentId);
        $this->assertSame('MA101', $recent[0]['course']->code);
    }

    public function test_department_breakdown(): void
    {
        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        Course::create(['name' => 'Civil', 'code' => 'CE1', 'credits' => 3, 'department_id' => $dept->id]);
        Lecturer::create(['first_name' => 'Dr', 'last_name' => 'Who', 'email' => 'who@uni.ac', 'department_id' => $dept->id]);

        $breakdown = $this->service->getDepartmentBreakdown();
        $this->assertCount(1, $breakdown);
        $this->assertSame('ENG', $breakdown[0]['department']->code);
        $this->assertSame(1, $breakdown[0]['course_count']);
        $this->assertSame(1, $breakdown[0]['lecturer_count']);
    }
}

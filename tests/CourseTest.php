<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Lecturer;
use App\Services\CourseService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Course model and CourseService.
 */
class CourseTest extends TestCase
{
    private static PDO $pdo;
    private CourseService $service;

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
                created_at TEXT NOT NULL DEFAULT (datetime("now")),
                updated_at TEXT NOT NULL DEFAULT (datetime("now"))
            );
            CREATE TABLE lecturers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT NOT NULL,
                last_name  TEXT NOT NULL,
                email      TEXT NOT NULL UNIQUE,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT (datetime("now")),
                updated_at TEXT NOT NULL DEFAULT (datetime("now"))
            );
            CREATE TABLE courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                code TEXT NOT NULL UNIQUE,
                description TEXT NULL,
                credits INTEGER NOT NULL DEFAULT 1,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT (datetime("now")),
                updated_at TEXT NOT NULL DEFAULT (datetime("now"))
            );
            CREATE TABLE course_lecturer (
                course_id   INTEGER NOT NULL REFERENCES courses(id),
                lecturer_id INTEGER NOT NULL REFERENCES lecturers(id),
                assigned_at TEXT NOT NULL DEFAULT (datetime("now")),
                PRIMARY KEY (course_id, lecturer_id)
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec('DELETE FROM course_lecturer; DELETE FROM courses; DELETE FROM lecturers; DELETE FROM departments;');
        $this->service = new CourseService();
    }

    // ------------------------------------------------------------------ //
    //  Create
    // ------------------------------------------------------------------ //

    public function test_create_course_succeeds_with_valid_data(): void
    {
        $course = $this->service->create([
            'name'    => 'Algorithms',
            'code'    => 'CS201',
            'credits' => 3,
        ]);

        $this->assertNotNull($course->id);
        $this->assertSame('Algorithms', $course->name);
        $this->assertSame('CS201', $course->code);
        $this->assertSame(3, $course->credits);
    }

    public function test_create_course_fails_when_name_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => '', 'code' => 'CS201', 'credits' => 3]);
    }

    public function test_create_course_fails_when_code_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Algorithms', 'code' => 'C 201', 'credits' => 3]);
    }

    public function test_create_course_fails_when_credits_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 0]);
    }

    public function test_create_course_fails_when_credits_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => -1]);
    }

    // ------------------------------------------------------------------ //
    //  Code uniqueness
    // ------------------------------------------------------------------ //

    public function test_duplicate_course_code_is_rejected(): void
    {
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already in use/i');
        $this->service->create(['name' => 'Different Course', 'code' => 'CS201', 'credits' => 2]);
    }

    public function test_duplicate_code_check_is_case_insensitive(): void
    {
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Other', 'code' => 'cs201', 'credits' => 2]);
    }

    // ------------------------------------------------------------------ //
    //  Search by code
    // ------------------------------------------------------------------ //

    public function test_search_by_code_returns_correct_course(): void
    {
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);
        $this->service->create(['name' => 'Calculus',   'code' => 'MA101', 'credits' => 4]);

        $found = $this->service->searchByCode('CS201');
        $this->assertNotNull($found);
        $this->assertSame('Algorithms', $found->name);
    }

    public function test_search_by_code_returns_null_for_nonexistent_code(): void
    {
        $this->assertNull($this->service->searchByCode('UNKNOWN'));
    }

    public function test_search_by_code_is_case_insensitive(): void
    {
        $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);
        $found = $this->service->searchByCode('cs201');
        $this->assertNotNull($found);
    }

    // ------------------------------------------------------------------ //
    //  Update
    // ------------------------------------------------------------------ //

    public function test_update_course_persists_changes(): void
    {
        $course  = $this->service->create(['name' => 'Old Name', 'code' => 'OLD01', 'credits' => 1]);
        $updated = $this->service->update($course, ['name' => 'New Name', 'credits' => 4]);

        $this->assertSame('New Name', $updated->name);
        $this->assertSame(4, $updated->credits);
    }

    public function test_update_rejects_duplicate_code_from_another_course(): void
    {
        $this->service->create(['name' => 'Calculus', 'code' => 'MA101', 'credits' => 4]);
        $algo = $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->update($algo, ['name' => 'Algorithms', 'code' => 'MA101']);
    }

    // ------------------------------------------------------------------ //
    //  Assign course to department
    // ------------------------------------------------------------------ //

    public function test_assign_course_to_department(): void
    {
        $dept   = Department::create(['name' => 'CS Dept', 'code' => 'CSD']);
        $course = $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);

        $this->service->update($course, ['department_id' => $dept->id]);
        $fresh = Course::findById($course->id);

        $this->assertSame($dept->id, $fresh->departmentId);
        $this->assertSame('CS Dept', $fresh->getDepartment()->name);
    }

    // ------------------------------------------------------------------ //
    //  Assign lecturer to course
    // ------------------------------------------------------------------ //

    public function test_assign_lecturer_to_course(): void
    {
        $course   = $this->service->create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);
        $lecturer = Lecturer::create(['first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@uni.ac']);

        $this->service->assignLecturer($course->id, $lecturer->id);

        $lecturers = Course::findById($course->id)->getLecturers();
        $this->assertCount(1, $lecturers);
        $this->assertSame('jane@uni.ac', $lecturers[0]->email);
    }

    public function test_assigning_same_lecturer_twice_is_idempotent(): void
    {
        $course   = $this->service->create(['name' => 'Calculus', 'code' => 'MA101', 'credits' => 4]);
        $lecturer = Lecturer::create(['first_name' => 'Bob', 'last_name' => 'Smith', 'email' => 'bob@uni.ac']);

        $this->service->assignLecturer($course->id, $lecturer->id);
        $this->service->assignLecturer($course->id, $lecturer->id); // should not throw

        $this->assertCount(1, Course::findById($course->id)->getLecturers());
    }

    public function test_assign_lecturer_to_nonexistent_course_throws(): void
    {
        $lecturer = Lecturer::create(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test@uni.ac']);
        $this->expectException(InvalidArgumentException::class);
        $this->service->assignLecturer(9999, $lecturer->id);
    }

    // ------------------------------------------------------------------ //
    //  Remove lecturer
    // ------------------------------------------------------------------ //

    public function test_remove_lecturer_from_course(): void
    {
        $course   = $this->service->create(['name' => 'Algorithms', 'code' => 'ALG01', 'credits' => 3]);
        $lecturer = Lecturer::create(['first_name' => 'Eve', 'last_name' => 'Dark', 'email' => 'eve@uni.ac']);

        $this->service->assignLecturer($course->id, $lecturer->id);
        $this->service->removeLecturer($course->id, $lecturer->id);

        $this->assertCount(0, Course::findById($course->id)->getLecturers());
    }
}

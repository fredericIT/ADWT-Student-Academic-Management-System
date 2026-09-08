<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Department;
use App\Models\Course;
use App\Models\Lecturer;
use App\Services\DepartmentService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Department model and DepartmentService.
 *
 * Uses an in-memory SQLite database — no live MySQL required.
 */
class DepartmentTest extends TestCase
{
    private static PDO $pdo;
    private DepartmentService $service;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // SQLite schema (mirrors MySQL DDL minus engine/charset)
        self::$pdo->exec(/** @lang SQLite */ '
            CREATE TABLE departments (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        TEXT    NOT NULL UNIQUE,
                code        TEXT    NOT NULL UNIQUE,
                description TEXT    NULL,
                created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE lecturers (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name    TEXT    NOT NULL,
                last_name     TEXT    NOT NULL,
                email         TEXT    NOT NULL UNIQUE,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE courses (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                name          TEXT    NOT NULL,
                code          TEXT    NOT NULL UNIQUE,
                description   TEXT    NULL,
                credits       INTEGER NOT NULL DEFAULT 1,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE course_lecturer (
                course_id   INTEGER NOT NULL REFERENCES courses(id),
                lecturer_id INTEGER NOT NULL REFERENCES lecturers(id),
                assigned_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (course_id, lecturer_id)
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        // Clear all tables before each test for isolation
        self::$pdo->exec('DELETE FROM course_lecturer; DELETE FROM courses; DELETE FROM lecturers; DELETE FROM departments;');
        $this->service = new DepartmentService();
    }

    // ------------------------------------------------------------------ //
    //  Create
    // ------------------------------------------------------------------ //

    public function test_create_department_succeeds_with_valid_data(): void
    {
        $dept = $this->service->create([
            'name'        => 'Computer Science',
            'code'        => 'CS',
            'description' => 'All things computing',
        ]);

        $this->assertNotNull($dept->id);
        $this->assertSame('Computer Science', $dept->name);
        $this->assertSame('CS', $dept->code);
        $this->assertSame('All things computing', $dept->description);
    }

    public function test_create_department_fails_when_name_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => '', 'code' => 'CS']);
    }

    public function test_create_department_fails_when_code_is_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Science', 'code' => 'X']);
    }

    public function test_create_department_fails_when_code_contains_spaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create(['name' => 'Science', 'code' => 'C S']);
    }

    // ------------------------------------------------------------------ //
    //  Uniqueness
    // ------------------------------------------------------------------ //

    public function test_duplicate_name_is_rejected(): void
    {
        $this->service->create(['name' => 'Physics', 'code' => 'PHY']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already exists/i');
        $this->service->create(['name' => 'Physics', 'code' => 'PH2']);
    }

    public function test_duplicate_code_is_rejected(): void
    {
        $this->service->create(['name' => 'Physics', 'code' => 'PHY']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already exists/i');
        $this->service->create(['name' => 'Astrophysics', 'code' => 'PHY']);
    }

    // ------------------------------------------------------------------ //
    //  Update
    // ------------------------------------------------------------------ //

    public function test_update_department_persists_changes(): void
    {
        $dept    = $this->service->create(['name' => 'Maths', 'code' => 'MTH']);
        $updated = $this->service->update($dept, ['name' => 'Mathematics', 'code' => 'MATH']);

        $this->assertSame('Mathematics', $updated->name);
        $this->assertSame('MATH', $updated->code);
    }

    public function test_update_rejects_duplicate_code_from_another_department(): void
    {
        $this->service->create(['name' => 'Biology', 'code' => 'BIO']);
        $chem = $this->service->create(['name' => 'Chemistry', 'code' => 'CHEM']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->update($chem, ['name' => 'Chemistry', 'code' => 'BIO']);
    }

    // ------------------------------------------------------------------ //
    //  Relationships
    // ------------------------------------------------------------------ //

    public function test_get_courses_returns_courses_belonging_to_department(): void
    {
        $dept = $this->service->create(['name' => 'CS Dept', 'code' => 'CSD']);
        Course::create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3, 'department_id' => $dept->id]);
        Course::create(['name' => 'Data Structures', 'code' => 'CS101', 'credits' => 3, 'department_id' => $dept->id]);
        // Course in a different dept (no dept)
        Course::create(['name' => 'Art History', 'code' => 'AH100', 'credits' => 2]);

        $courses = $dept->getCourses();
        $this->assertCount(2, $courses);
        $codes = array_map(fn($c) => $c->code, $courses);
        $this->assertContains('CS201', $codes);
        $this->assertContains('CS101', $codes);
    }

    public function test_get_lecturers_returns_lecturers_belonging_to_department(): void
    {
        $dept = $this->service->create(['name' => 'Engineering', 'code' => 'ENG']);
        Lecturer::create(['first_name' => 'Alice', 'last_name' => 'Brown', 'email' => 'alice@uni.ac', 'department_id' => $dept->id]);
        Lecturer::create(['first_name' => 'Bob',   'last_name' => 'Green', 'email' => 'bob@uni.ac']);

        $lecturers = $dept->getLecturers();
        $this->assertCount(1, $lecturers);
        $this->assertSame('alice@uni.ac', $lecturers[0]->email);
    }

    // ------------------------------------------------------------------ //
    //  Find All / By ID
    // ------------------------------------------------------------------ //

    public function test_find_all_returns_all_departments(): void
    {
        $this->service->create(['name' => 'Alpha', 'code' => 'ALP']);
        $this->service->create(['name' => 'Beta',  'code' => 'BET']);

        $all = Department::findAll();
        $this->assertCount(2, $all);
    }

    public function test_find_by_id_returns_null_for_missing_id(): void
    {
        $this->assertNull(Department::findById(9999));
    }
}

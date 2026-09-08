<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Lecturer;
use App\Services\LecturerService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Lecturer model and LecturerService.
 */
class LecturerTest extends TestCase
{
    private static PDO $pdo;
    private LecturerService $service;

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
                last_name  TEXT NOT NULL,
                email      TEXT NOT NULL UNIQUE,
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
                course_id   INTEGER NOT NULL REFERENCES courses(id),
                lecturer_id INTEGER NOT NULL REFERENCES lecturers(id),
                assigned_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (course_id, lecturer_id)
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec('DELETE FROM course_lecturer; DELETE FROM courses; DELETE FROM lecturers; DELETE FROM departments;');
        $this->service = new LecturerService();
    }

    // ------------------------------------------------------------------ //
    //  Register (create)
    // ------------------------------------------------------------------ //

    public function test_register_lecturer_succeeds_with_valid_data(): void
    {
        $lecturer = $this->service->register([
            'first_name' => 'Alice',
            'last_name'  => 'Johnson',
            'email'      => 'alice@university.ac',
        ]);

        $this->assertNotNull($lecturer->id);
        $this->assertSame('Alice', $lecturer->firstName);
        $this->assertSame('Johnson', $lecturer->lastName);
        $this->assertSame('alice@university.ac', $lecturer->email);
        $this->assertNull($lecturer->departmentId);
    }

    public function test_register_fails_when_first_name_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->register(['first_name' => '', 'last_name' => 'Doe', 'email' => 'x@uni.ac']);
    }

    public function test_register_fails_when_last_name_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->register(['first_name' => 'Jane', 'last_name' => '', 'email' => 'x@uni.ac']);
    }

    public function test_register_fails_with_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->register(['first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'not-an-email']);
    }

    public function test_register_fails_with_missing_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->register(['first_name' => 'Jane', 'last_name' => 'Doe', 'email' => '']);
    }

    // ------------------------------------------------------------------ //
    //  Email uniqueness
    // ------------------------------------------------------------------ //

    public function test_duplicate_email_is_rejected(): void
    {
        $this->service->register(['first_name' => 'Alice', 'last_name' => 'A', 'email' => 'shared@uni.ac']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already exists/i');
        $this->service->register(['first_name' => 'Bob', 'last_name' => 'B', 'email' => 'shared@uni.ac']);
    }

    public function test_email_uniqueness_is_case_insensitive(): void
    {
        $this->service->register(['first_name' => 'Alice', 'last_name' => 'A', 'email' => 'alice@uni.ac']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->register(['first_name' => 'Alice', 'last_name' => 'B', 'email' => 'ALICE@UNI.AC']);
    }

    // ------------------------------------------------------------------ //
    //  Update
    // ------------------------------------------------------------------ //

    public function test_update_lecturer_persists_changes(): void
    {
        $lecturer = $this->service->register(['first_name' => 'Old', 'last_name' => 'Name', 'email' => 'old@uni.ac']);
        $updated  = $this->service->update($lecturer, ['first_name' => 'New', 'last_name' => 'Updated']);

        $this->assertSame('New', $updated->firstName);
        $this->assertSame('Updated', $updated->lastName);
        $this->assertSame('old@uni.ac', $updated->email); // unchanged
    }

    public function test_update_rejects_email_already_used_by_another_lecturer(): void
    {
        $this->service->register(['first_name' => 'Alice', 'last_name' => 'A', 'email' => 'alice@uni.ac']);
        $bob = $this->service->register(['first_name' => 'Bob', 'last_name' => 'B', 'email' => 'bob@uni.ac']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->update($bob, ['email' => 'alice@uni.ac']);
    }

    // ------------------------------------------------------------------ //
    //  Associate with department
    // ------------------------------------------------------------------ //

    public function test_associate_department_updates_lecturer(): void
    {
        $dept     = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $lecturer = $this->service->register(['first_name' => 'Eve', 'last_name' => 'E', 'email' => 'eve@uni.ac']);

        $this->service->associateDepartment($lecturer, $dept->id);

        $fresh = Lecturer::findById($lecturer->id);
        $this->assertSame($dept->id, $fresh->departmentId);
        $this->assertSame('Engineering', $fresh->getDepartment()->name);
    }

    public function test_disassociate_department_sets_null(): void
    {
        $dept     = Department::create(['name' => 'Science', 'code' => 'SCI']);
        $lecturer = $this->service->register(['first_name' => 'Tom', 'last_name' => 'T', 'email' => 'tom@uni.ac', 'department_id' => $dept->id]);

        $this->service->associateDepartment($lecturer, null);

        $fresh = Lecturer::findById($lecturer->id);
        $this->assertNull($fresh->departmentId);
    }

    public function test_associate_with_nonexistent_department_throws(): void
    {
        $lecturer = $this->service->register(['first_name' => 'Sam', 'last_name' => 'S', 'email' => 'sam@uni.ac']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->associateDepartment($lecturer, 9999);
    }

    // ------------------------------------------------------------------ //
    //  Get assigned courses
    // ------------------------------------------------------------------ //

    public function test_get_courses_returns_assigned_courses(): void
    {
        $lecturer = $this->service->register(['first_name' => 'Prof', 'last_name' => 'X', 'email' => 'profx@uni.ac']);
        $c1 = Course::create(['name' => 'Algorithms', 'code' => 'CS201', 'credits' => 3]);
        $c2 = Course::create(['name' => 'Calculus',   'code' => 'MA101', 'credits' => 4]);

        $c1->assignLecturer($lecturer->id);
        $c2->assignLecturer($lecturer->id);

        $courses = Lecturer::findById($lecturer->id)->getCourses();
        $this->assertCount(2, $courses);
        $codes = array_map(fn($c) => $c->code, $courses);
        $this->assertContains('CS201', $codes);
        $this->assertContains('MA101', $codes);
    }

    public function test_get_courses_returns_empty_when_no_assignments(): void
    {
        $lecturer = $this->service->register(['first_name' => 'New', 'last_name' => 'Lecturer', 'email' => 'new@uni.ac']);
        $this->assertCount(0, $lecturer->getCourses());
    }

    // ------------------------------------------------------------------ //
    //  Full name
    // ------------------------------------------------------------------ //

    public function test_get_full_name_returns_first_and_last(): void
    {
        $lecturer = new Lecturer(firstName: 'Ada', lastName: 'Lovelace');
        $this->assertSame('Ada Lovelace', $lecturer->getFullName());
    }

    // ------------------------------------------------------------------ //
    //  Find All / By ID
    // ------------------------------------------------------------------ //

    public function test_find_all_returns_all_lecturers(): void
    {
        $this->service->register(['first_name' => 'A', 'last_name' => 'A', 'email' => 'a@uni.ac']);
        $this->service->register(['first_name' => 'B', 'last_name' => 'B', 'email' => 'b@uni.ac']);

        $this->assertCount(2, Lecturer::findAll());
    }

    public function test_find_by_id_returns_null_for_missing(): void
    {
        $this->assertNull(Lecturer::findById(9999));
    }
}

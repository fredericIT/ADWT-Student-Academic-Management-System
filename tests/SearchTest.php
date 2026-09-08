<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\SearchService;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SearchService and academic search requirements (Issue #7).
 */
class SearchTest extends TestCase
{
    private static PDO $pdo;
    private SearchService $service;

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
        self::$pdo->exec('DELETE FROM enrollments; DELETE FROM students; DELETE FROM courses; DELETE FROM departments;');
        $this->service = new SearchService();
    }

    private function seedTestData(): void
    {
        $dept = Department::create(['name' => 'Computer Science', 'code' => 'CS']);

        $c1 = Course::create(['name' => 'Web Technologies', 'code' => 'CS101', 'credits' => 3, 'department_id' => $dept->id]);
        $c2 = Course::create(['name' => 'Data Structures',  'code' => 'CS201', 'credits' => 4, 'department_id' => $dept->id]);

        $s1 = Student::create([
            'student_id'    => 'STU2026001',
            'first_name'    => 'Marthe',
            'last_name'     => 'Uwizeyimana',
            'email'         => 'marthe@student.ac.rw',
            'department_id' => $dept->id,
        ]);

        $s2 = Student::create([
            'student_id'    => 'STU2026002',
            'first_name'    => 'Frederic',
            'last_name'     => 'Ntawukuriryayo',
            'email'         => 'frederic@student.ac.rw',
            'department_id' => $dept->id,
        ]);

        Enrollment::create([
            'student_id' => $s1->id,
            'course_id'  => $c1->id,
            'status'     => Enrollment::STATUS_ACTIVE,
        ]);

        Enrollment::create([
            'student_id' => $s2->id,
            'course_id'  => $c1->id,
            'status'     => Enrollment::STATUS_ACTIVE,
        ]);

        Enrollment::create([
            'student_id' => $s1->id,
            'course_id'  => $c2->id,
            'status'     => Enrollment::STATUS_DROPPED,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  1. Search student by ID
    // ------------------------------------------------------------------ //

    public function test_search_student_by_exact_id(): void
    {
        $this->seedTestData();

        $results = $this->service->searchStudentsById('STU2026001');
        $this->assertCount(1, $results);
        $this->assertSame('STU2026001', $results[0]->studentId);
        $this->assertSame('Marthe', $results[0]->firstName);
    }

    public function test_search_student_by_partial_id(): void
    {
        $this->seedTestData();

        $results = $this->service->searchStudentsById('STU2026');
        $this->assertCount(2, $results);
    }

    // ------------------------------------------------------------------ //
    //  2. Search student by Name
    // ------------------------------------------------------------------ //

    public function test_search_student_by_first_name(): void
    {
        $this->seedTestData();

        $results = $this->service->searchStudentsByName('Marthe');
        $this->assertCount(1, $results);
        $this->assertSame('Uwizeyimana', $results[0]->lastName);
    }

    public function test_search_student_by_last_name(): void
    {
        $this->seedTestData();

        $results = $this->service->searchStudentsByName('Ntawukuriryayo');
        $this->assertCount(1, $results);
        $this->assertSame('Frederic', $results[0]->firstName);
    }

    public function test_search_student_by_full_name(): void
    {
        $this->seedTestData();

        $results = $this->service->searchStudentsByName('Marthe Uwizeyimana');
        $this->assertCount(1, $results);
        $this->assertSame('STU2026001', $results[0]->studentId);
    }

    // ------------------------------------------------------------------ //
    //  3. Search course by Course Code
    // ------------------------------------------------------------------ //

    public function test_search_course_by_code_case_insensitive(): void
    {
        $this->seedTestData();

        $results = $this->service->searchCoursesByCode('cs101');
        $this->assertCount(1, $results);
        $this->assertSame('CS101', $results[0]->code);
        $this->assertSame('Web Technologies', $results[0]->name);
    }

    public function test_search_course_by_name(): void
    {
        $this->seedTestData();

        $results = $this->service->searchCourses('Data Structures');
        $this->assertCount(1, $results);
        $this->assertSame('CS201', $results[0]->code);
    }

    // ------------------------------------------------------------------ //
    //  4. Find students registered for a course
    // ------------------------------------------------------------------ //

    public function test_get_students_registered_for_course_by_id(): void
    {
        $this->seedTestData();
        $course = Course::findByCode('CS101');

        $roster = $this->service->getStudentsByCourse($course->id);
        $this->assertNotNull($roster['course']);
        $this->assertCount(2, $roster['students']);
    }

    public function test_get_students_registered_for_course_by_code(): void
    {
        $this->seedTestData();

        $roster = $this->service->getStudentsByCourse('CS101');
        $this->assertNotNull($roster['course']);
        $this->assertCount(2, $roster['students']);
    }

    public function test_dropped_students_are_excluded_from_active_roster(): void
    {
        $this->seedTestData();

        $roster = $this->service->getStudentsByCourse('CS201');
        $this->assertNotNull($roster['course']);
        $this->assertCount(0, $roster['students']); // dropped enrollment
    }

    public function test_get_students_for_nonexistent_course_returns_empty(): void
    {
        $this->seedTestData();

        $roster = $this->service->getStudentsByCourse('UNKNOWN999');
        $this->assertNull($roster['course']);
        $this->assertCount(0, $roster['students']);
    }

    // ------------------------------------------------------------------ //
    //  5. Unified global search
    // ------------------------------------------------------------------ //

    public function test_global_search_returns_matching_entities(): void
    {
        $this->seedTestData();

        $res = $this->service->globalSearch('Marthe');
        $this->assertCount(1, $res['students']);
        $this->assertCount(0, $res['courses']);
        $this->assertSame(1, $res['total_results']);

        $res2 = $this->service->globalSearch('CS');
        $this->assertCount(2, $res2['courses']);
    }

    public function test_empty_query_returns_zero_results(): void
    {
        $this->seedTestData();

        $res = $this->service->globalSearch('');
        $this->assertSame(0, $res['total_results']);
        $this->assertCount(0, $res['students']);
        $this->assertCount(0, $res['courses']);
    }
}

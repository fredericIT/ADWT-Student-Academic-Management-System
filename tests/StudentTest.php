<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Address;
use App\Models\Department;
use App\Models\Student;
use App\Services\StudentService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Student model, Address model, and StudentService.
 *
 * Validates all Acceptance Criteria defined in Issue #3 (Student Management).
 */
class StudentTest extends TestCase
{
    private static PDO $pdo;
    private StudentService $service;

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
            CREATE TABLE addresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL UNIQUE REFERENCES students(id),
                province TEXT NOT NULL,
                district TEXT NOT NULL,
                sector TEXT NOT NULL,
                cell TEXT NOT NULL,
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
        self::$pdo->exec('DELETE FROM enrollments; DELETE FROM addresses; DELETE FROM students; DELETE FROM departments;');
        $this->service = new StudentService();
    }

    private function createDummyDepartment(string $name = 'Computer Science', string $code = 'CS'): Department
    {
        return Department::create([
            'name' => $name,
            'code' => $code,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Student Registration
    // ------------------------------------------------------------------ //

    public function test_administrator_can_register_student_successfully(): void
    {
        $dept = $this->createDummyDepartment();

        $student = $this->service->registerStudent([
            'student_id'    => 'STU2026001',
            'first_name'    => 'Jean',
            'last_name'     => 'Mugisha',
            'email'         => 'jean.mugisha@student.ac.rw',
            'department_id' => $dept->id,
            'province'      => 'Kigali City',
            'district'      => 'Nyarugenge',
            'sector'        => 'Nyamirambo',
            'cell'          => 'Rwezamenyo',
        ]);

        $this->assertNotNull($student->id);
        $this->assertSame('STU2026001', $student->studentId);
        $this->assertSame('Jean', $student->firstName);
        $this->assertSame('Mugisha', $student->lastName);
        $this->assertSame('Jean Mugisha', $student->getFullName());
        $this->assertSame('jean.mugisha@student.ac.rw', $student->email);
        $this->assertSame($dept->id, $student->departmentId);

        // Address check
        $address = $student->getAddress();
        $this->assertNotNull($address);
        $this->assertSame('Kigali City', $address->province);
        $this->assertSame('Nyarugenge', $address->district);
        $this->assertSame('Nyamirambo', $address->sector);
        $this->assertSame('Rwezamenyo', $address->cell);
        $this->assertSame('Rwezamenyo, Nyamirambo, Nyarugenge, Kigali City', $address->getFullAddress());
    }

    public function test_duplicate_student_ids_are_rejected(): void
    {
        $this->service->registerStudent([
            'student_id' => 'STU2026001',
            'first_name' => 'Alice',
            'last_name'  => 'Smith',
            'email'      => 'alice@uni.ac',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A student with Registration ID "STU2026001" already exists.');

        $this->service->registerStudent([
            'student_id' => 'STU2026001',
            'first_name' => 'Bob',
            'last_name'  => 'Jones',
            'email'      => 'bob@uni.ac',
        ]);
    }

    public function test_duplicate_emails_are_rejected(): void
    {
        $this->service->registerStudent([
            'student_id' => 'STU2026001',
            'first_name' => 'Alice',
            'last_name'  => 'Smith',
            'email'      => 'alice@uni.ac',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A student with Email address "alice@uni.ac" already exists.');

        $this->service->registerStudent([
            'student_id' => 'STU2026002',
            'first_name' => 'Bob',
            'last_name'  => 'Jones',
            'email'      => 'alice@uni.ac',
        ]);
    }

    public function test_invalid_input_data_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->registerStudent([
            'student_id' => '',
            'first_name' => 'A',
            'last_name'  => '',
            'email'      => 'invalid-email-format',
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Student Information Update & Address Update
    // ------------------------------------------------------------------ //

    public function test_student_information_and_address_can_be_updated(): void
    {
        $student = $this->service->registerStudent([
            'student_id' => 'STU2026001',
            'first_name' => 'Jean',
            'last_name'  => 'Mugisha',
            'email'      => 'jean@uni.ac',
            'province'   => 'Northern',
            'district'   => 'Musanze',
        ]);

        $updated = $this->service->updateStudent($student, [
            'first_name' => 'Jean-Paul',
            'last_name'  => 'Mugisha',
            'email'      => 'jp.mugisha@uni.ac',
            'province'   => 'Kigali City',
            'district'   => 'Gasabo',
            'sector'     => 'Kacyiru',
            'cell'       => 'Kamatamu',
        ]);

        $this->assertSame('Jean-Paul', $updated->firstName);
        $this->assertSame('jp.mugisha@uni.ac', $updated->email);

        $address = $updated->getAddress();
        $this->assertNotNull($address);
        $this->assertSame('Kigali City', $address->province);
        $this->assertSame('Gasabo', $address->district);
        $this->assertSame('Kacyiru', $address->sector);
        $this->assertSame('Kamatamu', $address->cell);
    }

    // ------------------------------------------------------------------ //
    //  Search Functionality (By ID and By Name)
    // ------------------------------------------------------------------ //

    public function test_student_can_be_searched_by_id(): void
    {
        $this->service->registerStudent([
            'student_id' => 'STU-1001',
            'first_name' => 'Alice',
            'last_name'  => 'Brown',
            'email'      => 'alice@uni.ac',
        ]);
        $this->service->registerStudent([
            'student_id' => 'STU-2002',
            'first_name' => 'Bob',
            'last_name'  => 'Green',
            'email'      => 'bob@uni.ac',
        ]);

        $found = $this->service->getByStudentId('STU-1001');
        $this->assertNotNull($found);
        $this->assertSame('Alice', $found->firstName);

        $searchResults = $this->service->search('STU-2002');
        $this->assertCount(1, $searchResults);
        $this->assertSame('Bob', $searchResults[0]->firstName);
    }

    public function test_student_can_be_searched_by_name(): void
    {
        $this->service->registerStudent([
            'student_id' => 'STU-101',
            'first_name' => 'Emmanuel',
            'last_name'  => 'Habimana',
            'email'      => 'e.habimana@uni.ac',
        ]);
        $this->service->registerStudent([
            'student_id' => 'STU-102',
            'first_name' => 'Clarie',
            'last_name'  => 'Uwamahoro',
            'email'      => 'c.uwamahoro@uni.ac',
        ]);

        // Search by first name
        $results = Student::searchByName('Emmanuel');
        $this->assertCount(1, $results);
        $this->assertSame('STU-101', $results[0]->studentId);

        // Search by last name
        $results2 = Student::searchByName('Uwamahoro');
        $this->assertCount(1, $results2);
        $this->assertSame('STU-102', $results2[0]->studentId);

        // Search by combined full name term using service search
        $results3 = $this->service->search('Emmanuel Habimana');
        $this->assertCount(1, $results3);
        $this->assertSame('STU-101', $results3[0]->studentId);
    }

    // ------------------------------------------------------------------ //
    //  Department & Profile Linkage
    // ------------------------------------------------------------------ //

    public function test_student_belongs_to_correct_department(): void
    {
        $dept1 = $this->createDummyDepartment('Software Engineering', 'SE');
        $dept2 = $this->createDummyDepartment('Information Technology', 'IT');

        $student = $this->service->registerStudent([
            'student_id'    => 'SE-2026-01',
            'first_name'    => 'David',
            'last_name'     => 'Kamanzi',
            'email'         => 'david@uni.ac',
            'department_id' => $dept1->id,
        ]);

        $this->assertNotNull($student->getDepartment());
        $this->assertSame('Software Engineering', $student->getDepartment()->name);
        $this->assertSame('SE', $student->getDepartment()->code);

        // Update department to IT
        $this->service->updateStudent($student, ['department_id' => $dept2->id]);
        $this->assertSame('Information Technology', $student->getDepartment()->name);
    }
}

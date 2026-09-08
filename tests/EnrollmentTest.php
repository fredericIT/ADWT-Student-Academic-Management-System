<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Enrollment model, Student model, and EnrollmentService.
 *
 * Validates all Acceptance Criteria and Business Rules defined in Issue #5.
 */
class EnrollmentTest extends TestCase
{
    private static PDO $pdo;
    private EnrollmentService $service;

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
            CREATE TABLE students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id TEXT NOT NULL UNIQUE,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                department_id INTEGER NULL REFERENCES departments(id),
                created_at TEXT NOT NULL DEFAULT (datetime("now")),
                updated_at TEXT NOT NULL DEFAULT (datetime("now"))
            );
            CREATE TABLE enrollments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL REFERENCES students(id),
                course_id INTEGER NOT NULL REFERENCES courses(id),
                status TEXT NOT NULL DEFAULT "active",
                enrollment_date TEXT NOT NULL DEFAULT (datetime("now")),
                created_at TEXT NOT NULL DEFAULT (datetime("now")),
                updated_at TEXT NOT NULL DEFAULT (datetime("now"))
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec('DELETE FROM enrollments; DELETE FROM students; DELETE FROM courses; DELETE FROM departments;');
        $this->service = new EnrollmentService();
    }

    private function createDummyStudent(string $studentId = 'STD001', string $email = 'alice@uni.ac'): Student
    {
        return Student::create([
            'student_id' => $studentId,
            'first_name' => 'Alice',
            'last_name'  => 'Smith',
            'email'      => $email,
        ]);
    }

    private function createDummyCourse(string $code = 'CS101', string $name = 'Computer Science I'): Course
    {
        return Course::create([
            'name'    => $name,
            'code'    => $code,
            'credits' => 3,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Registration / Enrollment
    // ------------------------------------------------------------------ //

    public function test_student_can_register_for_course_successfully(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $enrollment = $this->service->enroll($student->id, $course->id);

        $this->assertNotNull($enrollment->id);
        $this->assertSame($student->id, $enrollment->studentId);
        $this->assertSame($course->id, $enrollment->courseId);
        $this->assertSame(Enrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertTrue($enrollment->isActive());
        $this->assertNotNull($enrollment->enrollmentDate);
    }

    public function test_duplicate_active_enrollment_is_prevented(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $this->service->enroll($student->id, $course->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Student is already enrolled in this course.');
        $this->service->enroll($student->id, $course->id);
    }

    public function test_enrollment_fails_when_student_does_not_exist(): void
    {
        $course = $this->createDummyCourse();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Student with ID 9999 not found.');
        $this->service->enroll(9999, $course->id);
    }

    public function test_enrollment_fails_when_course_does_not_exist(): void
    {
        $student = $this->createDummyStudent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Course with ID 9999 not found.');
        $this->service->enroll($student->id, 9999);
    }

    // ------------------------------------------------------------------ //
    //  Drop Course
    // ------------------------------------------------------------------ //

    public function test_student_can_drop_course(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $enrollment = $this->service->enroll($student->id, $course->id);
        $dropped    = $this->service->drop($enrollment->id);

        $this->assertSame(Enrollment::STATUS_DROPPED, $dropped->status);
        $this->assertFalse($dropped->isActive());

        // Verify state is persisted in database
        $fresh = Enrollment::findById($enrollment->id);
        $this->assertNotNull($fresh);
        $this->assertSame(Enrollment::STATUS_DROPPED, $fresh->status);
    }

    public function test_drop_course_by_student_and_course(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $this->service->enroll($student->id, $course->id);
        $dropped = $this->service->dropByStudentAndCourse($student->id, $course->id);

        $this->assertSame(Enrollment::STATUS_DROPPED, $dropped->status);
    }

    public function test_dropping_already_dropped_course_throws_exception(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $enrollment = $this->service->enroll($student->id, $course->id);
        $this->service->drop($enrollment->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Course enrollment is not currently active.');
        $this->service->drop($enrollment->id);
    }

    public function test_dropping_nonexistent_enrollment_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Enrollment with ID 9999 not found.');
        $this->service->drop(9999);
    }

    // ------------------------------------------------------------------ //
    //  Re-enrollment
    // ------------------------------------------------------------------ //

    public function test_student_can_re_enroll_in_previously_dropped_course(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $enrollment = $this->service->enroll($student->id, $course->id);
        $this->service->drop($enrollment->id);

        // Re-enroll
        $reEnrolled = $this->service->enroll($student->id, $course->id);

        $this->assertSame($enrollment->id, $reEnrolled->id);
        $this->assertSame(Enrollment::STATUS_ACTIVE, $reEnrolled->status);
        $this->assertTrue($reEnrolled->isActive());
    }

    // ------------------------------------------------------------------ //
    //  View registered courses / course roster
    // ------------------------------------------------------------------ //

    public function test_get_student_enrollments(): void
    {
        $student = $this->createDummyStudent();
        $course1 = $this->createDummyCourse('CS101', 'Intro to CS');
        $course2 = $this->createDummyCourse('MA101', 'Calculus');

        $this->service->enroll($student->id, $course1->id);
        $enr2 = $this->service->enroll($student->id, $course2->id);
        $this->service->drop($enr2->id);

        // All enrollments
        $all = $this->service->getStudentEnrollments($student->id, false);
        $this->assertCount(2, $all);

        // Active only
        $activeOnly = $this->service->getStudentEnrollments($student->id, true);
        $this->assertCount(1, $activeOnly);
        $this->assertSame($course1->id, $activeOnly[0]->courseId);
    }

    public function test_get_students_registered_for_course(): void
    {
        $student1 = $this->createDummyStudent('STD001', 's1@uni.ac');
        $student2 = $this->createDummyStudent('STD002', 's2@uni.ac');
        $course   = $this->createDummyCourse('CS202', 'Data Structures');

        $this->service->enroll($student1->id, $course->id);
        $this->service->enroll($student2->id, $course->id);

        $roster = $this->service->getCourseStudents($course->id);
        $this->assertCount(2, $roster);

        $emails = array_map(fn(Student $s) => $s->email, $roster);
        $this->assertContains('s1@uni.ac', $emails);
        $this->assertContains('s2@uni.ac', $emails);

        // Dropping student2 removes them from active roster
        $this->service->dropByStudentAndCourse($student2->id, $course->id);
        $updatedRoster = $this->service->getCourseStudents($course->id);
        $this->assertCount(1, $updatedRoster);
        $this->assertSame($student1->id, $updatedRoster[0]->id);
    }

    // ------------------------------------------------------------------ //
    //  Model relationships
    // ------------------------------------------------------------------ //

    public function test_model_relationships_work_properly(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $enrollment = $this->service->enroll($student->id, $course->id);

        $this->assertSame($student->id, $enrollment->getStudent()->id);
        $this->assertSame($course->id, $enrollment->getCourse()->id);

        $studentCourses = $student->getCourses(true);
        $this->assertCount(1, $studentCourses);
        $this->assertSame($course->id, $studentCourses[0]->id);

        $courseStudents = $course->getEnrolledStudents();
        $this->assertCount(1, $courseStudents);
        $this->assertSame($student->id, $courseStudents[0]->id);
    }
}

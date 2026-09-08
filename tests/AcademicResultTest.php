<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Connection;
use App\Models\AcademicRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AcademicService;
use App\Services\EnrollmentService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Grade, AcademicRecord, and AcademicService.
 *
 * Validates all Acceptance Criteria and Business Rules defined in Issue #6:
 * - Authorized lecturer can record marks.
 * - Authorized lecturer can update marks.
 * - Unauthorized users cannot modify marks.
 * - Invalid marks (< 0 or > 100) are rejected.
 * - Student can view academic results.
 * - Academic record is correctly associated with the student.
 * - Average is calculated correctly.
 * - Pass/fail status is calculated correctly.
 * - Backend follows OOP design.
 */
class AcademicResultTest extends TestCase
{
    private static PDO $pdo;
    private AcademicService $service;
    private EnrollmentService $enrollmentService;

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
            CREATE TABLE enrollments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL REFERENCES students(id),
                course_id INTEGER NOT NULL REFERENCES courses(id),
                status TEXT NOT NULL DEFAULT "active",
                enrollment_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE academic_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL UNIQUE REFERENCES students(id),
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE grades (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                academic_record_id INTEGER NOT NULL REFERENCES academic_records(id),
                student_id INTEGER NOT NULL REFERENCES students(id),
                course_id INTEGER NOT NULL REFERENCES courses(id),
                lecturer_id INTEGER NULL REFERENCES lecturers(id),
                mark REAL NOT NULL,
                letter_grade TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT "PASS",
                remarks TEXT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (student_id, course_id)
            );
        ');

        Connection::setInstance(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec('
            DELETE FROM grades;
            DELETE FROM academic_records;
            DELETE FROM enrollments;
            DELETE FROM course_lecturer;
            DELETE FROM courses;
            DELETE FROM lecturers;
            DELETE FROM students;
            DELETE FROM departments;
        ');

        $this->service           = new AcademicService();
        $this->enrollmentService = new EnrollmentService();
    }

    private function createDummyStudent(string $studentId = 'STD001', string $email = 'student@uni.ac'): Student
    {
        return Student::create([
            'student_id' => $studentId,
            'first_name' => 'Alice',
            'last_name'  => 'Mugabe',
            'email'      => $email,
        ]);
    }

    private function createDummyCourse(string $code = 'CS101', string $name = 'Intro to Programming', int $credits = 3): Course
    {
        return Course::create([
            'name'    => $name,
            'code'    => $code,
            'credits' => $credits,
        ]);
    }

    private function createDummyLecturer(string $email = 'lecturer@uni.ac'): Lecturer
    {
        return Lecturer::create([
            'first_name' => 'Eric',
            'last_name'  => 'Gatete',
            'email'      => $email,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Mark Recording Tests
    // ------------------------------------------------------------------ //

    public function test_authorized_lecturer_can_record_marks_successfully(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $lecturer = $this->createDummyLecturer();

        // Assign lecturer to course and enroll student
        $course->assignLecturer($lecturer->id);
        $this->enrollmentService->enroll($student->id, $course->id);

        $grade = $this->service->recordMark($student->id, $course->id, 85.5, $lecturer->id, 'Good performance');

        $this->assertNotNull($grade->id);
        $this->assertSame($student->id, $grade->studentId);
        $this->assertSame($course->id, $grade->courseId);
        $this->assertSame($lecturer->id, $grade->lecturerId);
        $this->assertSame(85.5, $grade->mark);
        $this->assertSame('A', $grade->letterGrade);
        $this->assertSame('PASS', $grade->status);
        $this->assertTrue($grade->isPass());
        $this->assertSame('Good performance', $grade->remarks);
    }

    public function test_grade_automatically_assigned_status_and_letter_grade(): void
    {
        $this->assertSame('A', Grade::calculateLetterGrade(92.0));
        $this->assertSame('A', Grade::calculateLetterGrade(80.0));
        $this->assertSame('B', Grade::calculateLetterGrade(79.9));
        $this->assertSame('B', Grade::calculateLetterGrade(70.0));
        $this->assertSame('C', Grade::calculateLetterGrade(65.0));
        $this->assertSame('D', Grade::calculateLetterGrade(50.0));
        $this->assertSame('F', Grade::calculateLetterGrade(49.9));
        $this->assertSame('F', Grade::calculateLetterGrade(10.0));

        $this->assertSame('PASS', Grade::determineStatus(50.0));
        $this->assertSame('PASS', Grade::determineStatus(85.0));
        $this->assertSame('FAIL', Grade::determineStatus(49.9));
        $this->assertSame('FAIL', Grade::determineStatus(0.0));
    }

    public function test_invalid_marks_below_zero_are_rejected(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $this->enrollmentService->enroll($student->id, $course->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid mark. Mark must be within the allowed range.');

        $this->service->recordMark($student->id, $course->id, -5.0);
    }

    public function test_invalid_marks_above_one_hundred_are_rejected(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $this->enrollmentService->enroll($student->id, $course->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid mark. Mark must be within the allowed range.');

        $this->service->recordMark($student->id, $course->id, 105.0);
    }

    public function test_recording_mark_for_unenrolled_student_is_rejected(): void
    {
        $student = $this->createDummyStudent();
        $course  = $this->createDummyCourse();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Student is not actively enrolled in this course.');

        $this->service->recordMark($student->id, $course->id, 75.0);
    }

    public function test_recording_mark_for_nonexistent_student_is_rejected(): void
    {
        $course = $this->createDummyCourse();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Student record not found.');

        $this->service->recordMark(9999, $course->id, 75.0);
    }

    public function test_recording_mark_for_nonexistent_course_is_rejected(): void
    {
        $student = $this->createDummyStudent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Course not found.');

        $this->service->recordMark($student->id, 9999, 75.0);
    }

    // ------------------------------------------------------------------ //
    //  Authorization Tests
    // ------------------------------------------------------------------ //

    public function test_unauthorized_lecturer_cannot_record_marks_for_unassigned_course(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $lecturer = $this->createDummyLecturer();

        // Lecturer is NOT assigned to $course
        $this->enrollmentService->enroll($student->id, $course->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You are not authorized to update this result.');

        $this->service->recordMark($student->id, $course->id, 80.0, $lecturer->id);
    }

    public function test_unauthorized_lecturer_cannot_update_marks(): void
    {
        $student   = $this->createDummyStudent();
        $course    = $this->createDummyCourse();
        $assigned  = $this->createDummyLecturer('assigned@uni.ac');
        $unauth    = $this->createDummyLecturer('unauth@uni.ac');

        $course->assignLecturer($assigned->id);
        $this->enrollmentService->enroll($student->id, $course->id);

        $grade = $this->service->recordMark($student->id, $course->id, 65.0, $assigned->id);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You are not authorized to update this result.');

        $this->service->updateMark($grade->id, 90.0, $unauth->id);
    }

    // ------------------------------------------------------------------ //
    //  Mark Update Tests
    // ------------------------------------------------------------------ //

    public function test_authorized_lecturer_can_update_marks(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $lecturer = $this->createDummyLecturer();

        $course->assignLecturer($lecturer->id);
        $this->enrollmentService->enroll($student->id, $course->id);

        $grade = $this->service->recordMark($student->id, $course->id, 45.0, $lecturer->id);
        $this->assertSame('F', $grade->letterGrade);
        $this->assertSame('FAIL', $grade->status);

        // Update mark after re-evaluation
        $updated = $this->service->updateMark($grade->id, 55.0, $lecturer->id, 'Remarked after review');

        $this->assertSame(55.0, $updated->mark);
        $this->assertSame('D', $updated->letterGrade);
        $this->assertSame('PASS', $updated->status);
        $this->assertSame('Remarked after review', $updated->remarks);

        // Verify persisted state
        $fresh = Grade::findById($grade->id);
        $this->assertNotNull($fresh);
        $this->assertSame(55.0, $fresh->mark);
        $this->assertSame('PASS', $fresh->status);
    }

    // ------------------------------------------------------------------ //
    //  Academic Record & Average Calculation Tests
    // ------------------------------------------------------------------ //

    public function test_academic_record_is_correctly_associated_with_student(): void
    {
        $student = $this->createDummyStudent();
        $record  = $this->service->getOrCreateAcademicRecord($student->id);

        $this->assertNotNull($record->id);
        $this->assertSame($student->id, $record->studentId);
        $this->assertSame($student->id, $record->getStudent()->id);

        // Relationship from Student model
        $studentRecord = $student->getAcademicRecord();
        $this->assertNotNull($studentRecord);
        $this->assertSame($record->id, $studentRecord->id);
    }

    public function test_calculate_average_from_valid_grades(): void
    {
        $student = $this->createDummyStudent();
        $c1 = $this->createDummyCourse('CS101', 'Programming I', 3);
        $c2 = $this->createDummyCourse('CS102', 'Programming II', 3);
        $c3 = $this->createDummyCourse('MA101', 'Calculus', 4);

        $this->enrollmentService->enroll($student->id, $c1->id);
        $this->enrollmentService->enroll($student->id, $c2->id);
        $this->enrollmentService->enroll($student->id, $c3->id);

        $this->service->recordMark($student->id, $c1->id, 70.0);
        $this->service->recordMark($student->id, $c2->id, 80.0);
        $this->service->recordMark($student->id, $c3->id, 60.0);

        // Expected average: (70 + 80 + 60) / 3 = 70.00
        $avg = $this->service->calculateAverage($student->id);
        $this->assertSame(70.0, $avg);

        $record = $this->service->getStudentAcademicRecord($student->id);
        $this->assertNotNull($record);
        $this->assertSame(70.0, $record->calculateAverage());

        // Weighted average: (70*3 + 80*3 + 60*4) / (3 + 3 + 4) = (210 + 240 + 240) / 10 = 69.00
        $this->assertSame(69.0, $record->calculateWeightedAverage());
        $this->assertSame(10, $record->getTotalCredits());
    }

    public function test_pass_fail_status_calculated_correctly_for_individual_grade(): void
    {
        $student = $this->createDummyStudent();
        $c1 = $this->createDummyCourse('CS101', 'Programming I');
        $c2 = $this->createDummyCourse('CS102', 'Programming II');

        $this->enrollmentService->enroll($student->id, $c1->id);
        $this->enrollmentService->enroll($student->id, $c2->id);

        $g1 = $this->service->recordMark($student->id, $c1->id, 49.0);
        $g2 = $this->service->recordMark($student->id, $c2->id, 50.0);

        $this->assertFalse($g1->isPass());
        $this->assertSame('FAIL', $g1->status);

        $this->assertTrue($g2->isPass());
        $this->assertSame('PASS', $g2->status);
    }

    public function test_overall_academic_record_pass_fail_determination(): void
    {
        $student = $this->createDummyStudent();
        $c1 = $this->createDummyCourse('CS101', 'Course 1');
        $c2 = $this->createDummyCourse('CS102', 'Course 2');

        $this->enrollmentService->enroll($student->id, $c1->id);
        $this->enrollmentService->enroll($student->id, $c2->id);

        // Average: (40 + 40) / 2 = 40.0 -> FAIL
        $this->service->recordMark($student->id, $c1->id, 40.0);
        $this->service->recordMark($student->id, $c2->id, 40.0);

        $this->assertSame('FAIL', $this->service->determinePassFail($student->id));

        // Improving marks: (60 + 60) / 2 = 60.0 -> PASS
        $this->service->recordMark($student->id, $c1->id, 60.0);
        $this->service->recordMark($student->id, $c2->id, 60.0);

        $this->assertSame('PASS', $this->service->determinePassFail($student->id));
    }

    public function test_model_relationships_work_properly(): void
    {
        $student  = $this->createDummyStudent();
        $course   = $this->createDummyCourse();
        $lecturer = $this->createDummyLecturer();

        $course->assignLecturer($lecturer->id);
        $this->enrollmentService->enroll($student->id, $course->id);

        $grade = $this->service->recordMark($student->id, $course->id, 95.0, $lecturer->id);

        // Relationship assertions
        $this->assertSame($student->id, $grade->getStudent()->id);
        $this->assertSame($course->id, $grade->getCourse()->id);
        $this->assertSame($lecturer->id, $grade->getLecturer()->id);
        $this->assertNotNull($grade->getAcademicRecord());

        // Reverse relationships
        $studentGrades = $student->getGrades();
        $this->assertCount(1, $studentGrades);
        $this->assertSame($grade->id, $studentGrades[0]->id);

        $courseGrades = $course->getGrades();
        $this->assertCount(1, $courseGrades);
        $this->assertSame($grade->id, $courseGrades[0]->id);

        $this->assertTrue($course->hasLecturer($lecturer->id));
    }
}

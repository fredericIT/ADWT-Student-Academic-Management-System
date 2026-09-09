<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Exceptions\DuplicateEnrollmentException;
use App\Exceptions\StudentNotFoundException;
use App\Exceptions\CourseNotFoundException;
use InvalidArgumentException;

/**
 * Business logic layer for student course enrollment and registration.
 */
class EnrollmentService
{
    /**
     * Register a student for a course.
     *
     * Validates that both the student and course exist and prevents duplicate active enrollment.
     * If the student previously dropped the course, the enrollment is reactivated.
     *
     * @throws InvalidArgumentException
     */
    public function enroll(int $studentId, int $courseId): Enrollment
    {
        $student = Student::findById($studentId);
        if (!$student) {
            throw new StudentNotFoundException("Student with ID {$studentId} not found.");
        }

        $course = Course::findById($courseId);
        if (!$course) {
            throw new CourseNotFoundException("Course with ID {$courseId} not found.");
        }

        $existing = Enrollment::findByStudentAndCourse($studentId, $courseId);
        if ($existing !== null) {
            if ($existing->isActive()) {
                throw new DuplicateEnrollmentException('Student is already enrolled in this course.');
            }
            return $existing->reactivate();
        }

        return Enrollment::create([
            'student_id' => $studentId,
            'course_id'  => $courseId,
            'status'     => Enrollment::STATUS_ACTIVE,
        ]);
    }

    /**
     * Drop a course enrollment by enrollment ID.
     *
     * @throws InvalidArgumentException
     */
    public function drop(int $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::findById($enrollmentId);
        if (!$enrollment) {
            throw new InvalidArgumentException("Enrollment with ID {$enrollmentId} not found.");
        }

        if (!$enrollment->isActive()) {
            throw new InvalidArgumentException('Course enrollment is not currently active.');
        }

        return $enrollment->drop();
    }

    /**
     * Drop a course enrollment by student ID and course ID.
     *
     * @throws InvalidArgumentException
     */
    public function dropByStudentAndCourse(int $studentId, int $courseId): Enrollment
    {
        $enrollment = Enrollment::findActive($studentId, $courseId);
        if (!$enrollment) {
            throw new InvalidArgumentException('Active enrollment not found for this student and course.');
        }

        return $enrollment->drop();
    }

    /**
     * Return all enrollments for a given student.
     *
     * @return Enrollment[]
     * @throws InvalidArgumentException
     */
    public function getStudentEnrollments(int $studentId, bool $activeOnly = false): array
    {
        $student = Student::findById($studentId);
        if (!$student) {
            throw new InvalidArgumentException("Student with ID {$studentId} not found.");
        }

        return Enrollment::findByStudent($studentId, $activeOnly);
    }

    /**
     * Return all students actively registered in a given course.
     *
     * @return Student[]
     * @throws InvalidArgumentException
     */
    public function getCourseStudents(int $courseId): array
    {
        $course = Course::findById($courseId);
        if (!$course) {
            throw new InvalidArgumentException("Course with ID {$courseId} not found.");
        }

        return $course->getEnrolledStudents();
    }

    /**
     * Return all enrollment records.
     *
     * @return Enrollment[]
     */
    public function getAll(): array
    {
        return Enrollment::findAll();
    }

    /**
     * Find an enrollment by ID.
     */
    public function getById(int $id): ?Enrollment
    {
        return Enrollment::findById($id);
    }
}

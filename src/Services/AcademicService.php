<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicRecord;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Lecturer;
use App\Models\Student;
use App\Exceptions\InvalidMarkException;
use App\Exceptions\StudentNotFoundException;
use App\Exceptions\CourseNotFoundException;
use App\Exceptions\UnauthorizedActionException;
use InvalidArgumentException;

/**
 * Business logic layer for Academic Results and Records management.
 *
 * Enforces business rules:
 * - Marks must be within allowed range (0.0 - 100.0).
 * - Only authorized users/lecturers can record and update marks.
 * - Grades must belong to valid students actively enrolled in the course.
 * - Automatic letter grade and pass/fail determination.
 * - Calculation of student academic averages and overall status.
 */
class AcademicService
{
    /**
     * Record a student mark for a course.
     *
     * @throws InvalidArgumentException
     */
    public function recordMark(
        int $studentId,
        int $courseId,
        float $mark,
        ?int $lecturerId = null,
        ?string $remarks = null
    ): Grade {
        // Validate mark range
        if (!Grade::isValidMark($mark)) {
            throw new InvalidMarkException('Invalid mark. Mark must be within the allowed range.');
        }

        // Verify student existence
        $student = Student::findById($studentId);
        if (!$student) {
            throw new StudentNotFoundException('Student record not found.');
        }

        // Verify course existence
        $course = Course::findById($courseId);
        if (!$course) {
            throw new CourseNotFoundException('Course not found.');
        }

        // Verify enrollment (student must have an active enrollment in this course)
        $enrollment = Enrollment::findActive($studentId, $courseId);
        if (!$enrollment) {
            throw new InvalidArgumentException('Student is not actively enrolled in this course.');
        }

        // Authorization check: If a lecturer ID is provided, verify lecturer is assigned to this course
        if ($lecturerId !== null) {
            $this->assertLecturerAuthorized($lecturerId, $course);
        }

        // Retrieve or initialize student's AcademicRecord
        $record = AcademicRecord::getOrCreateForStudent($studentId);

        // Check if grade already exists for this student and course
        $existing = Grade::findByStudentAndCourse($studentId, $courseId);
        if ($existing !== null) {
            return $existing->update([
                'mark'        => $mark,
                'lecturer_id' => $lecturerId,
                'remarks'     => $remarks,
            ]);
        }

        return Grade::create([
            'academic_record_id' => $record->id,
            'student_id'         => $studentId,
            'course_id'          => $courseId,
            'lecturer_id'        => $lecturerId,
            'mark'               => $mark,
            'remarks'            => $remarks,
        ]);
    }

    /**
     * Update an existing student mark.
     *
     * @throws InvalidArgumentException
     */
    public function updateMark(
        int $gradeId,
        float $newMark,
        ?int $lecturerId = null,
        ?string $remarks = null
    ): Grade {
        if (!Grade::isValidMark($newMark)) {
            throw new InvalidArgumentException('Invalid mark. Mark must be within the allowed range.');
        }

        $grade = Grade::findById($gradeId);
        if (!$grade) {
            throw new InvalidArgumentException('Grade record not found.');
        }

        if ($lecturerId !== null) {
            $course = $grade->getCourse();
            if ($course) {
                $this->assertLecturerAuthorized($lecturerId, $course);
            }
        }

        return $grade->update([
            'mark'        => $newMark,
            'lecturer_id' => $lecturerId,
            'remarks'     => $remarks,
        ]);
    }

    /**
     * Retrieve a student's complete academic record.
     */
    public function getStudentAcademicRecord(int $studentId): ?AcademicRecord
    {
        return AcademicRecord::findByStudentId($studentId);
    }

    /**
     * Retrieve or instantiate an academic record for a student.
     */
    public function getOrCreateAcademicRecord(int $studentId): AcademicRecord
    {
        return AcademicRecord::getOrCreateForStudent($studentId);
    }

    /**
     * Retrieve a student's complete academic record with all aggregated grades.
     * Accepts either the integer database primary key or the string student registration ID.
     */
    public function getStudentResults(int|string $studentId): AcademicRecord
    {
        if (is_string($studentId) && !ctype_digit($studentId)) {
            $student = Student::findByStudentId($studentId);
            $id = $student ? $student->id : 0;
        } else {
            $id = (int) $studentId;
        }

        return $this->getOrCreateAcademicRecord($id);
    }

    /**
     * Return all grades recorded for a student.
     *
     * @return Grade[]
     */
    public function getStudentGrades(int $studentId): array
    {
        return Grade::findByStudent($studentId);
    }

    /**
     * Return all grades recorded for a specific course.
     *
     * @return Grade[]
     */
    public function getCourseGrades(int $courseId): array
    {
        return Grade::findByCourse($courseId);
    }

    /**
     * Calculate the student's arithmetic average mark.
     */
    public function calculateAverage(int $studentId): float
    {
        $record = AcademicRecord::findByStudentId($studentId);
        return $record ? $record->calculateAverage() : 0.0;
    }

    /**
     * Determine a student's overall pass/fail status.
     */
    public function determinePassFail(int $studentId): string
    {
        $record = AcademicRecord::findByStudentId($studentId);
        return $record ? $record->getOverallStatus() : Grade::STATUS_FAIL;
    }

    /**
     * Retrieve a grade by its primary key.
     */
    public function getGradeById(int $gradeId): ?Grade
    {
        return Grade::findById($gradeId);
    }

    /**
     * Return all grades in the system.
     *
     * @return Grade[]
     */
    public function getAllGrades(): array
    {
        return Grade::findAll();
    }

    public function deleteGrade(Grade $grade): bool
    {
        return $grade->delete();
    }

    /**
     * Compute high-level academic statistics across the system.
     *
     * @return array{
     *     total_grades: int,
     *     passed_count: int,
     *     failed_count: int,
     *     average_mark: float,
     *     pass_rate: float
     * }
     */
    public function getStatistics(): array
    {
        $grades = Grade::findAll();
        $total  = count($grades);
        if ($total === 0) {
            return [
                'total_grades' => 0,
                'passed_count' => 0,
                'failed_count' => 0,
                'average_mark' => 0.0,
                'pass_rate'    => 0.0,
            ];
        }

        $passed = count(array_filter($grades, fn(Grade $g) => $g->isPass()));
        $failed = $total - $passed;
        $sum    = array_reduce($grades, fn(float $acc, Grade $g) => $acc + $g->mark, 0.0);
        $avg    = round($sum / $total, 2);
        $rate   = round(($passed / $total) * 100, 1);

        return [
            'total_grades' => $total,
            'passed_count' => $passed,
            'failed_count' => $failed,
            'average_mark' => $avg,
            'pass_rate'    => $rate,
        ];
    }

    // ------------------------------------------------------------------ //
    //  Private Helpers
    // ------------------------------------------------------------------ //

    /**
     * Assert that a lecturer is authorized to record/update marks for a given course.
     *
     * @throws InvalidArgumentException
     */
    private function assertLecturerAuthorized(int $lecturerId, Course $course): void
    {
        $lecturer = Lecturer::findById($lecturerId);
        if (!$lecturer) {
            throw new InvalidArgumentException("Lecturer with ID {$lecturerId} not found.");
        }

        if (!$course->hasLecturer($lecturerId)) {
            throw new UnauthorizedActionException('You are not authorized to update this result.');
        }
    }
}

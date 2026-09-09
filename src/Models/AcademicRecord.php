<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents the cumulative academic record of a Student.
 *
 * Aggregates all Grade entries for the student, calculates average marks,
 * weighted GPA/average, total accumulated credits, and determines overall
 * academic pass/fail status.
 */
class AcademicRecord
{
    public function __construct(
        public ?int            $id        = null,
        public int             $studentId = 0,
        public ?string         $createdAt = null,
        public ?string         $updatedAt = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Calculations & Academic Metrics
    // ------------------------------------------------------------------ //

    /**
     * Return all grades associated with this academic record.
     *
     * @return Grade[]
     */
    public function getGrades(): array
    {
        return $this->id !== null ? Grade::findByAcademicRecord($this->id) : [];
    }

    /**
     * Calculate the simple arithmetic average of all recorded marks.
     */
    public function calculateAverage(): float
    {
        $grades = $this->getGrades();
        if (empty($grades)) {
            return 0.0;
        }

        $sum = array_reduce($grades, fn(float $acc, Grade $g) => $acc + $g->mark, 0.0);
        return round($sum / count($grades), 2);
    }

    /**
     * Calculate the credit-weighted average mark.
     */
    public function calculateWeightedAverage(): float
    {
        $grades = $this->getGrades();
        if (empty($grades)) {
            return 0.0;
        }

        $totalWeightedPoints = 0.0;
        $totalCredits        = 0;

        foreach ($grades as $grade) {
            $course = $grade->getCourse();
            $credits = $course ? max(1, $course->credits) : 1;
            $totalWeightedPoints += $grade->mark * $credits;
            $totalCredits        += $credits;
        }

        return $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.0;
    }

    /**
     * Calculate cumulative GPA on a 4.0 scale weighted by course credit hours.
     * Scale: A = 4.0, B = 3.0, C = 2.0, D = 1.0, F = 0.0.
     */
    public function calculateGPA(): float
    {
        $grades = $this->getGrades();
        if (empty($grades)) {
            return 0.0;
        }

        $totalPoints  = 0.0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $course  = $grade->getCourse();
            $credits = $course ? max(1, $course->credits) : 1;
            $gpaPoints = match ($grade->letterGrade) {
                'A'     => 4.0,
                'B'     => 3.0,
                'C'     => 2.0,
                'D'     => 1.0,
                default => 0.0,
            };
            $totalPoints  += $gpaPoints * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    /**
     * Determine the overall academic pass/fail status.
     * Evaluates whether the student's arithmetic average meets the PASS_MARK threshold.
     */
    public function getOverallStatus(): string
    {
        $grades = $this->getGrades();
        if (empty($grades)) {
            return Grade::STATUS_FAIL;
        }

        return $this->calculateAverage() >= Grade::PASS_MARK ? Grade::STATUS_PASS : Grade::STATUS_FAIL;
    }

    /**
     * Alias for getOverallStatus() matching class design specifications.
     */
    public function determineOverallStatus(): string
    {
        return $this->getOverallStatus();
    }

    /**
     * Return the total credit hours attempted.
     */
    public function getTotalCredits(): int
    {
        $grades  = $this->getGrades();
        $credits = 0;
        foreach ($grades as $grade) {
            $course = $grade->getCourse();
            $credits += $course ? max(1, $course->credits) : 1;
        }
        return $credits;
    }

    /**
     * Return the total credit hours earned (passed courses only).
     */
    public function getEarnedCredits(): int
    {
        $grades  = $this->getGrades();
        $credits = 0;
        foreach ($grades as $grade) {
            if ($grade->isPass()) {
                $course = $grade->getCourse();
                $credits += $course ? max(1, $course->credits) : 1;
            }
        }
        return $credits;
    }

    public function getPassedCoursesCount(): int
    {
        return count(array_filter($this->getGrades(), fn(Grade $g) => $g->isPass()));
    }

    public function getFailedCoursesCount(): int
    {
        return count(array_filter($this->getGrades(), fn(Grade $g) => !$g->isPass()));
    }

    // ------------------------------------------------------------------ //
    //  Static Finders
    // ------------------------------------------------------------------ //

    /**
     * Return all academic records ordered by ID.
     *
     * @return AcademicRecord[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM academic_records ORDER BY id ASC');
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find an academic record by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM academic_records WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find an academic record for a specific student.
     */
    public static function findByStudentId(int $studentId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM academic_records WHERE student_id = :sid LIMIT 1');
        $stmt->execute([':sid' => $studentId]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Retrieve existing academic record for a student or create one if it does not yet exist.
     */
    public static function getOrCreateForStudent(int $studentId): self
    {
        $existing = self::findByStudentId($studentId);
        if ($existing !== null) {
            return $existing;
        }

        return self::create(['student_id' => $studentId]);
    }

    // ------------------------------------------------------------------ //
    //  Write Operations
    // ------------------------------------------------------------------ //

    /**
     * Create a new academic record for a student.
     *
     * @param array{student_id: int} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('INSERT INTO academic_records (student_id) VALUES (:student_id)');
        $stmt->execute([':student_id' => (int) $data['student_id']]);
        return self::findById((int) $pdo->lastInsertId());
    }

    // ------------------------------------------------------------------ //
    //  Relationship Accessors
    // ------------------------------------------------------------------ //

    public function getStudent(): ?Student
    {
        return Student::findById($this->studentId);
    }

    // ------------------------------------------------------------------ //
    //  Serialization & Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:        (int) $row['id'],
            studentId: (int) $row['student_id'],
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        $student = $this->getStudent();
        return [
            'id'               => $this->id,
            'student_id'       => $this->studentId,
            'student_name'     => $student ? $student->getFullName() : null,
            'average_mark'     => $this->calculateAverage(),
            'weighted_average' => $this->calculateWeightedAverage(),
            'total_credits'    => $this->getTotalCredits(),
            'earned_credits'   => $this->getEarnedCredits(),
            'status'           => $this->getOverallStatus(),
            'grades_count'     => count($this->getGrades()),
            'passed_count'     => $this->getPassedCoursesCount(),
            'failed_count'     => $this->getFailedCoursesCount(),
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}

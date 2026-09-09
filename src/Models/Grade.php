<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents an academic grade/result awarded to a Student for a Course.
 *
 * Encapsulates mark storage, letter grade calculation, pass/fail determination,
 * database operations for the `grades` table, and relationships to Student,
 * Course, Lecturer, and AcademicRecord.
 */
class Grade
{
    public const MIN_MARK    = 0.0;
    public const MAX_MARK    = 100.0;
    public const PASS_MARK   = 50.0;
    public const STATUS_PASS = 'PASS';
    public const STATUS_FAIL = 'FAIL';

    public function __construct(
        public ?int            $id               = null,
        public int             $academicRecordId = 0,
        public int             $studentId        = 0,
        public int             $courseId         = 0,
        public ?int            $lecturerId       = null,
        public float           $mark             = 0.0,
        public string          $letterGrade      = '',
        public string          $status           = self::STATUS_PASS,
        public ?string         $remarks          = null,
        public ?string         $createdAt        = null,
        public ?string         $updatedAt        = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Grading Logic & Status Checks
    // ------------------------------------------------------------------ //

    public function isPass(): bool
    {
        return strtoupper($this->status) === self::STATUS_PASS;
    }

    public static function isValidMark(float $mark): bool
    {
        return $mark >= self::MIN_MARK && $mark <= self::MAX_MARK;
    }

    /**
     * Determine the letter grade based on numeric mark (0-100 scale).
     */
    public static function calculateLetterGrade(float $mark): string
    {
        if ($mark >= 80.0) {
            return 'A';
        }
        if ($mark >= 70.0) {
            return 'B';
        }
        if ($mark >= 60.0) {
            return 'C';
        }
        if ($mark >= 50.0) {
            return 'D';
        }
        return 'F';
    }

    /**
     * Determine PASS or FAIL status based on configured pass mark.
     */
    public static function determineStatus(float $mark): string
    {
        return $mark >= self::PASS_MARK ? self::STATUS_PASS : self::STATUS_FAIL;
    }

    // ------------------------------------------------------------------ //
    //  Static Finders
    // ------------------------------------------------------------------ //

    /**
     * Return all grades ordered by creation date descending.
     *
     * @return Grade[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM grades ORDER BY id DESC');
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find a grade by its primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM grades WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find a grade by student ID and course ID.
     */
    public static function findByStudentAndCourse(int $studentId, int $courseId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM grades WHERE student_id = :sid AND course_id = :cid LIMIT 1'
        );
        $stmt->execute([':sid' => $studentId, ':cid' => $courseId]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find all grades belonging to an academic record.
     *
     * @return Grade[]
     */
    public static function findByAcademicRecord(int $academicRecordId): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM grades WHERE academic_record_id = :arid ORDER BY id ASC');
        $stmt->execute([':arid' => $academicRecordId]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find all grades awarded to a student.
     *
     * @return Grade[]
     */
    public static function findByStudent(int $studentId): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM grades WHERE student_id = :sid ORDER BY id ASC');
        $stmt->execute([':sid' => $studentId]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find all grades awarded for a specific course.
     *
     * @return Grade[]
     */
    public static function findByCourse(int $courseId): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM grades WHERE course_id = :cid ORDER BY mark DESC');
        $stmt->execute([':cid' => $courseId]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    // ------------------------------------------------------------------ //
    //  Write Operations
    // ------------------------------------------------------------------ //

    /**
     * Insert a new grade row and return the populated instance.
     *
     * @param array{
     *     academic_record_id: int,
     *     student_id: int,
     *     course_id: int,
     *     mark: float,
     *     lecturer_id?: int|null,
     *     remarks?: string|null
     * } $data
     */
    public static function create(array $data): self
    {
        $mark        = (float) $data['mark'];
        $letterGrade = self::calculateLetterGrade($mark);
        $status      = self::determineStatus($mark);

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO grades (academic_record_id, student_id, course_id, lecturer_id, mark, letter_grade, status, remarks)
             VALUES (:academic_record_id, :student_id, :course_id, :lecturer_id, :mark, :letter_grade, :status, :remarks)'
        );
        $stmt->execute([
            ':academic_record_id' => (int) $data['academic_record_id'],
            ':student_id'         => (int) $data['student_id'],
            ':course_id'          => (int) $data['course_id'],
            ':lecturer_id'        => !empty($data['lecturer_id']) ? (int) $data['lecturer_id'] : null,
            ':mark'               => $mark,
            ':letter_grade'       => $letterGrade,
            ':status'             => $status,
            ':remarks'            => !empty($data['remarks']) ? trim((string) $data['remarks']) : null,
        ]);

        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update mark, status, and remarks for this grade.
     *
     * @param array{mark?: float, lecturer_id?: int|null, remarks?: string|null} $data
     */
    public function update(array $data): self
    {
        if (isset($data['mark'])) {
            $this->mark        = (float) $data['mark'];
            $this->letterGrade = self::calculateLetterGrade($this->mark);
            $this->status      = self::determineStatus($this->mark);
        }

        if (array_key_exists('lecturer_id', $data)) {
            $this->lecturerId = !empty($data['lecturer_id']) ? (int) $data['lecturer_id'] : null;
        }

        if (array_key_exists('remarks', $data)) {
            $this->remarks = !empty($data['remarks']) ? trim((string) $data['remarks']) : null;
        }

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE grades
                SET mark = :mark, letter_grade = :letter_grade, status = :status,
                    lecturer_id = :lecturer_id, remarks = :remarks
              WHERE id = :id'
        );
        $stmt->execute([
            ':mark'         => $this->mark,
            ':letter_grade' => $this->letterGrade,
            ':status'       => $this->status,
            ':lecturer_id'  => $this->lecturerId,
            ':remarks'      => $this->remarks,
            ':id'           => $this->id,
        ]);

        return $this;
    }

    /**
     * Delete this grade record.
     */
    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('DELETE FROM grades WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }

    // ------------------------------------------------------------------ //
    //  Relationship Accessors
    // ------------------------------------------------------------------ //

    public function getStudent(): ?Student
    {
        return Student::findById($this->studentId);
    }

    public function getCourse(): ?Course
    {
        return Course::findById($this->courseId);
    }

    public function getLecturer(): ?Lecturer
    {
        return $this->lecturerId !== null ? Lecturer::findById($this->lecturerId) : null;
    }

    public function getAcademicRecord(): ?AcademicRecord
    {
        return AcademicRecord::findById($this->academicRecordId);
    }

    // ------------------------------------------------------------------ //
    //  Serialization & Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        $mark        = (float) ($row['mark'] ?? 0.0);
        $letterGrade = (string) ($row['letter_grade'] ?? $row['grade_letter'] ?? self::calculateLetterGrade($mark));
        $status      = (string) ($row['status'] ?? self::determineStatus($mark));

        $studentId   = (int) ($row['student_id'] ?? 0);
        $recordId    = (int) ($row['academic_record_id'] ?? 0);
        if ($studentId === 0 && $recordId > 0) {
            $ar = AcademicRecord::findById($recordId);
            if ($ar !== null) {
                $studentId = (int) $ar->studentId;
            }
        }

        return new self(
            id:               (int) $row['id'],
            academicRecordId: $recordId,
            studentId:        $studentId,
            courseId:         (int) $row['course_id'],
            lecturerId:       isset($row['lecturer_id']) && $row['lecturer_id'] !== null ? (int) $row['lecturer_id'] : null,
            mark:             $mark,
            letterGrade:      $letterGrade,
            status:           $status,
            remarks:          $row['remarks'] ?? null,
            createdAt:        $row['created_at'] ?? $row['recorded_at'] ?? null,
            updatedAt:        $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        $course = $this->getCourse();
        return [
            'id'                 => $this->id,
            'academic_record_id' => $this->academicRecordId,
            'student_id'         => $this->studentId,
            'course_id'          => $this->courseId,
            'course_code'        => $course ? $course->code : null,
            'course_name'        => $course ? $course->name : null,
            'credits'            => $course ? $course->credits : 1,
            'lecturer_id'        => $this->lecturerId,
            'mark'               => $this->mark,
            'letter_grade'       => $this->letterGrade,
            'status'             => $this->status,
            'remarks'            => $this->remarks,
            'created_at'         => $this->createdAt,
            'updated_at'         => $this->updatedAt,
        ];
    }
}

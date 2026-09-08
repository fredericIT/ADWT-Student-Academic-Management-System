<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents an academic enrollment linking a Student to a Course.
 *
 * Encapsulates database operations for the `enrollments` table, tracks
 * status ('active' | 'dropped') and enrollment dates, and exposes
 * relationship accessors to the associated Student and Course.
 */
class Enrollment
{
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_DROPPED = 'dropped';

    public function __construct(
        public ?int             $id             = null,
        public int              $studentId      = 0,
        public int              $courseId       = 0,
        public string           $status         = self::STATUS_ACTIVE,
        public ?string          $enrollmentDate = null,
        public ?string          $createdAt      = null,
        public ?string          $updatedAt      = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Status checks & transitions
    // ------------------------------------------------------------------ //

    public function isActive(): bool
    {
        return strtolower($this->status) === self::STATUS_ACTIVE;
    }

    /**
     * Drop this course enrollment (transitions status to 'dropped').
     */
    public function drop(): self
    {
        $this->status = self::STATUS_DROPPED;
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('UPDATE enrollments SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => self::STATUS_DROPPED,
            ':id'     => $this->id,
        ]);
        return $this;
    }

    /**
     * Reactivate a previously dropped enrollment.
     */
    public function reactivate(): self
    {
        $this->status         = self::STATUS_ACTIVE;
        $this->enrollmentDate = date('Y-m-d H:i:s');

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE enrollments
                SET status = :status, enrollment_date = :enrollment_date
              WHERE id = :id'
        );
        $stmt->execute([
            ':status'          => self::STATUS_ACTIVE,
            ':enrollment_date' => $this->enrollmentDate,
            ':id'              => $this->id,
        ]);
        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Static finders
    // ------------------------------------------------------------------ //

    /**
     * Return all enrollments ordered by enrollment date descending.
     *
     * @return Enrollment[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM enrollments ORDER BY enrollment_date DESC, id DESC');
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find a single enrollment by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM enrollments WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find an enrollment record by student ID and course ID.
     */
    public static function findByStudentAndCourse(int $studentId, int $courseId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM enrollments WHERE student_id = :sid AND course_id = :cid LIMIT 1'
        );
        $stmt->execute([':sid' => $studentId, ':cid' => $courseId]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find an active enrollment for a specific student and course.
     */
    public static function findActive(int $studentId, int $courseId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM enrollments
              WHERE student_id = :sid AND course_id = :cid AND status = :status
              LIMIT 1'
        );
        $stmt->execute([
            ':sid'    => $studentId,
            ':cid'    => $courseId,
            ':status' => self::STATUS_ACTIVE,
        ]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Return all enrollments for a given student.
     *
     * @return Enrollment[]
     */
    public static function findByStudent(int $studentId, bool $activeOnly = false): array
    {
        $pdo = Connection::getInstance();
        $sql = 'SELECT * FROM enrollments WHERE student_id = :sid';
        if ($activeOnly) {
            $sql .= ' AND status = "active"';
        }
        $sql .= ' ORDER BY enrollment_date DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => $studentId]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Return all enrollments for a given course.
     *
     * @return Enrollment[]
     */
    public static function findByCourse(int $courseId, bool $activeOnly = true): array
    {
        $pdo = Connection::getInstance();
        $sql = 'SELECT * FROM enrollments WHERE course_id = :cid';
        if ($activeOnly) {
            $sql .= ' AND status = "active"';
        }
        $sql .= ' ORDER BY enrollment_date DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cid' => $courseId]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    // ------------------------------------------------------------------ //
    //  Write operations
    // ------------------------------------------------------------------ //

    /**
     * Create a new enrollment record and return the populated instance.
     *
     * @param array{student_id: int, course_id: int, status?: string} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO enrollments (student_id, course_id, status)
             VALUES (:student_id, :course_id, :status)'
        );
        $stmt->execute([
            ':student_id' => $data['student_id'],
            ':course_id'  => $data['course_id'],
            ':status'     => $data['status'] ?? self::STATUS_ACTIVE,
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    // ------------------------------------------------------------------ //
    //  Relationship accessors
    // ------------------------------------------------------------------ //

    public function getStudent(): ?Student
    {
        return Student::findById($this->studentId);
    }

    public function getCourse(): ?Course
    {
        return Course::findById($this->courseId);
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:             (int) $row['id'],
            studentId:      (int) $row['student_id'],
            courseId:       (int) $row['course_id'],
            status:               $row['status'],
            enrollmentDate:       $row['enrollment_date'] ?? null,
            createdAt:            $row['created_at']      ?? null,
            updatedAt:            $row['updated_at']      ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'student_id'      => $this->studentId,
            'course_id'       => $this->courseId,
            'status'          => $this->status,
            'enrollment_date' => $this->enrollmentDate,
            'created_at'      => $this->createdAt,
            'updated_at'      => $this->updatedAt,
        ];
    }
}

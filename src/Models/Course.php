<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents an academic course.
 *
 * Encapsulates database operations for the `courses` table and provides
 * relationship accessors for the owning Department and assigned Lecturers.
 */
class Course
{
    public function __construct(
        public ?int             $id           = null,
        public string           $name         = '',
        public string           $code         = '',
        public ?string          $description  = null,
        public int              $credits      = 1,
        public ?int             $departmentId = null,
        public ?string          $createdAt    = null,
        public ?string          $updatedAt    = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Static finders
    // ------------------------------------------------------------------ //

    /**
     * Return all courses ordered by code.
     *
     * @return Course[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM courses ORDER BY code');
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find a single course by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find a course by its unique code (case-insensitive).
     */
    public static function findByCode(string $code): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE UPPER(code) = UPPER(:code) LIMIT 1');
        $stmt->execute([':code' => $code]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    // ------------------------------------------------------------------ //
    //  Write operations
    // ------------------------------------------------------------------ //

    /**
     * Create a new course row and return the populated instance.
     *
     * @param array{name: string, code: string, credits: int, description?: string|null, department_id?: int|null} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO courses (name, code, credits, description, department_id)
             VALUES (:name, :code, :credits, :description, :department_id)'
        );
        $stmt->execute([
            ':name'          => $data['name'],
            ':code'          => $data['code'],
            ':credits'       => $data['credits'],
            ':description'   => $data['description']   ?? null,
            ':department_id' => $data['department_id'] ?? null,
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update mutable fields on this course.
     *
     * @param array{name?: string, code?: string, credits?: int, description?: string|null, department_id?: int|null} $data
     */
    public function update(array $data): self
    {
        $this->name         = $data['name']         ?? $this->name;
        $this->code         = $data['code']          ?? $this->code;
        $this->credits      = $data['credits']       ?? $this->credits;
        $this->description  = array_key_exists('description', $data)
            ? $data['description']
            : $this->description;
        $this->departmentId = array_key_exists('department_id', $data)
            ? $data['department_id']
            : $this->departmentId;

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE courses
                SET name = :name, code = :code, credits = :credits,
                    description = :description, department_id = :department_id
              WHERE id = :id'
        );
        $stmt->execute([
            ':name'          => $this->name,
            ':code'          => $this->code,
            ':credits'       => $this->credits,
            ':description'   => $this->description,
            ':department_id' => $this->departmentId,
            ':id'            => $this->id,
        ]);
        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Relationship accessors
    // ------------------------------------------------------------------ //

    /**
     * Return the department this course belongs to, or null.
     */
    public function getDepartment(): ?Department
    {
        return $this->departmentId !== null
            ? Department::findById($this->departmentId)
            : null;
    }

    /**
     * Return all lecturers assigned to this course.
     *
     * @return Lecturer[]
     */
    public function getLecturers(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT l.*
               FROM lecturers l
               JOIN course_lecturer cl ON cl.lecturer_id = l.id
              WHERE cl.course_id = :course_id
              ORDER BY l.last_name, l.first_name'
        );
        $stmt->execute([':course_id' => $this->id]);
        return array_map(fn(array $row) => Lecturer::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Assign a lecturer to this course (idempotent — uses INSERT IGNORE).
     */
    public function assignLecturer(int $lecturerId): void
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO course_lecturer (course_id, lecturer_id)
             SELECT :cid1, :lid1
              WHERE NOT EXISTS (
                  SELECT 1 FROM course_lecturer WHERE course_id = :cid2 AND lecturer_id = :lid2
              )'
        );
        $stmt->execute([
            ':cid1' => $this->id,
            ':lid1' => $lecturerId,
            ':cid2' => $this->id,
            ':lid2' => $lecturerId,
        ]);
    }

    /**
     * Remove a lecturer from this course.
     */
    public function removeLecturer(int $lecturerId): void
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'DELETE FROM course_lecturer WHERE course_id = :cid AND lecturer_id = :lid'
        );
        $stmt->execute([':cid' => $this->id, ':lid' => $lecturerId]);
    }

    /**
     * Return all enrollments for this course.
     *
     * @return Enrollment[]
     */
    public function getEnrollments(bool $activeOnly = true): array
    {
        return Enrollment::findByCourse($this->id, $activeOnly);
    }

    /**
     * Return all students actively enrolled in this course.
     *
     * @return Student[]
     */
    public function getEnrolledStudents(): array
    {
        $enrollments = $this->getEnrollments(true);
        $students    = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->getStudent();
            if ($student !== null) {
                $students[] = $student;
            }
        }
        return $students;
    }

    /**
     * Return all grades recorded for this course.
     *
     * @return Grade[]
     */
    public function getGrades(): array
    {
        return $this->id !== null ? Grade::findByCourse($this->id) : [];
    }

    /**
     * Check if a specific lecturer is assigned to teach this course.
     */
    public function hasLecturer(int $lecturerId): bool
    {
        if ($this->id === null) {
            return false;
        }
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT 1 FROM course_lecturer WHERE course_id = :cid AND lecturer_id = :lid LIMIT 1'
        );
        $stmt->execute([':cid' => $this->id, ':lid' => $lecturerId]);
        return (bool) $stmt->fetchColumn();
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int) $row['id'],
            name:               $row['name'],
            code:               $row['code'],
            description:        $row['description']  ?? null,
            credits:      (int) $row['credits'],
            departmentId: isset($row['department_id']) ? (int) $row['department_id'] : null,
            createdAt:          $row['created_at']   ?? null,
            updatedAt:          $row['updated_at']   ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'code'          => $this->code,
            'description'   => $this->description,
            'credits'       => $this->credits,
            'department_id' => $this->departmentId,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}

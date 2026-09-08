<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents a student within the academic system.
 *
 * Encapsulates database operations for the `students` table and provides
 * relationship accessors for the student's Department and Enrollments.
 */
class Student
{
    public function __construct(
        public readonly ?int    $id           = null,
        public string           $studentId    = '',
        public string           $firstName    = '',
        public string           $lastName     = '',
        public string           $email        = '',
        public ?int             $departmentId = null,
        public ?string          $createdAt    = null,
        public ?string          $updatedAt    = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Computed properties
    // ------------------------------------------------------------------ //

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    // ------------------------------------------------------------------ //
    //  Static finders
    // ------------------------------------------------------------------ //

    /**
     * Return all students ordered by surname then first name.
     *
     * @return Student[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM students ORDER BY last_name, first_name');
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find a single student by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find a student by student registration ID (case-insensitive).
     */
    public static function findByStudentId(string $studentId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM students WHERE UPPER(student_id) = UPPER(:sid) LIMIT 1');
        $stmt->execute([':sid' => trim($studentId)]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find a student by email address (case-insensitive).
     */
    public static function findByEmail(string $email): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM students WHERE UPPER(email) = UPPER(:email) LIMIT 1');
        $stmt->execute([':email' => trim($email)]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    // ------------------------------------------------------------------ //
    //  Write operations
    // ------------------------------------------------------------------ //

    /**
     * Register a new student and return the populated instance.
     *
     * @param array{student_id: string, first_name: string, last_name: string, email: string, department_id?: int|null} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO students (student_id, first_name, last_name, email, department_id)
             VALUES (:student_id, :first_name, :last_name, :email, :department_id)'
        );
        $stmt->execute([
            ':student_id'    => $data['student_id'],
            ':first_name'    => $data['first_name'],
            ':last_name'     => $data['last_name'],
            ':email'         => $data['email'],
            ':department_id' => $data['department_id'] ?? null,
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update this student's mutable fields.
     *
     * @param array{student_id?: string, first_name?: string, last_name?: string, email?: string, department_id?: int|null} $data
     */
    public function update(array $data): self
    {
        $this->studentId    = $data['student_id']    ?? $this->studentId;
        $this->firstName    = $data['first_name']    ?? $this->firstName;
        $this->lastName     = $data['last_name']     ?? $this->lastName;
        $this->email        = $data['email']         ?? $this->email;
        $this->departmentId = array_key_exists('department_id', $data)
            ? $data['department_id']
            : $this->departmentId;

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE students
                SET student_id = :student_id, first_name = :first_name, last_name = :last_name,
                    email = :email, department_id = :department_id
              WHERE id = :id'
        );
        $stmt->execute([
            ':student_id'    => $this->studentId,
            ':first_name'    => $this->firstName,
            ':last_name'     => $this->lastName,
            ':email'         => $this->email,
            ':department_id' => $this->departmentId,
            ':id'            => $this->id,
        ]);
        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Relationship accessors
    // ------------------------------------------------------------------ //

    public function getDepartment(): ?Department
    {
        return $this->departmentId !== null
            ? Department::findById($this->departmentId)
            : null;
    }

    /**
     * Return all enrollments for this student.
     *
     * @return Enrollment[]
     */
    public function getEnrollments(bool $activeOnly = false): array
    {
        return Enrollment::findByStudent($this->id, $activeOnly);
    }

    /**
     * Return all courses this student is actively enrolled in.
     *
     * @return Course[]
     */
    public function getCourses(bool $activeOnly = true): array
    {
        $enrollments = $this->getEnrollments($activeOnly);
        $courses = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->getCourse();
            if ($course !== null) {
                $courses[] = $course;
            }
        }
        return $courses;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int) $row['id'],
            studentId:          $row['student_id'],
            firstName:          $row['first_name'],
            lastName:           $row['last_name'],
            email:              $row['email'],
            departmentId: isset($row['department_id']) ? (int) $row['department_id'] : null,
            createdAt:          $row['created_at']   ?? null,
            updatedAt:          $row['updated_at']   ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'student_id'    => $this->studentId,
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'full_name'     => $this->getFullName(),
            'email'         => $this->email,
            'department_id' => $this->departmentId,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}

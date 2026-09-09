<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents a lecturer (faculty member).
 *
 * Extends User and encapsulates database operations for the `lecturers` table
 * while providing relationship accessors for the associated Department and assigned Courses.
 */
class Lecturer extends User
{
    public string $lecturerId;

    public function __construct(
        public readonly ?int    $id           = null,
        public string           $firstName    = '',
        public string           $lastName     = '',
        public string           $email        = '',
        public ?int             $departmentId = null,
        public ?string          $createdAt    = null,
        public ?string          $updatedAt    = null,
        string                  $lecturerId   = '',
        string                  $password     = '',
    ) {
        $fullName = trim("{$firstName} {$lastName}");
        parent::__construct(
            userId: $id ?? 0,
            name: $fullName,
            email: $email,
            password: $password
        );
        $this->lecturerId = $lecturerId !== '' ? $lecturerId : (string) ($id ?? '');
    }

    // ------------------------------------------------------------------ //
    //  Role & UML methods
    // ------------------------------------------------------------------ //

    public function getRole(): string
    {
        return 'Lecturer';
    }

    public function recordMark(): bool
    {
        return true;
    }

    public function updateMark(): bool
    {
        return true;
    }

    public function viewMarks(): array
    {
        return [];
    }

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
     * Return all lecturers ordered by surname then first name.
     *
     * @return Lecturer[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query(
            'SELECT * FROM lecturers ORDER BY last_name, first_name'
        );
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Find a single lecturer by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM lecturers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    // ------------------------------------------------------------------ //
    //  Write operations
    // ------------------------------------------------------------------ //

    /**
     * Register (create) a new lecturer and return the populated instance.
     *
     * @param array{first_name: string, last_name: string, email: string, department_id?: int|null} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO lecturers (first_name, last_name, email, department_id)
             VALUES (:first_name, :last_name, :email, :department_id)'
        );
        $stmt->execute([
            ':first_name'    => $data['first_name'],
            ':last_name'     => $data['last_name'],
            ':email'         => $data['email'],
            ':department_id' => $data['department_id'] ?? null,
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update this lecturer's mutable fields.
     *
     * @param array{first_name?: string, last_name?: string, email?: string, department_id?: int|null} $data
     */
    public function update(array $data): self
    {
        $this->firstName    = $data['first_name']   ?? $this->firstName;
        $this->lastName     = $data['last_name']    ?? $this->lastName;
        $this->email        = $data['email']         ?? $this->email;
        $this->name         = trim("{$this->firstName} {$this->lastName}");
        $this->departmentId = array_key_exists('department_id', $data)
            ? $data['department_id']
            : $this->departmentId;

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE lecturers
                SET first_name = :first_name, last_name = :last_name,
                    email = :email, department_id = :department_id
              WHERE id = :id'
        );
        $stmt->execute([
            ':first_name'    => $this->firstName,
            ':last_name'     => $this->lastName,
            ':email'         => $this->email,
            ':department_id' => $this->departmentId,
            ':id'            => $this->id,
        ]);
        return $this;
    }

    /**
     * Associate this lecturer with a department (or disassociate by passing null).
     */
    public function associateDepartment(?int $departmentId): void
    {
        $this->update(['department_id' => $departmentId]);
    }

    // ------------------------------------------------------------------ //
    //  Relationship accessors
    // ------------------------------------------------------------------ //

    /**
     * Return the department this lecturer belongs to, or null.
     */
    public function getDepartment(): ?Department
    {
        return $this->departmentId !== null
            ? Department::findById($this->departmentId)
            : null;
    }

    /**
     * Return all courses assigned to this lecturer.
     *
     * @return Course[]
     */
    public function getCourses(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT c.*
               FROM courses c
               JOIN course_lecturer cl ON cl.course_id = c.id
              WHERE cl.lecturer_id = :lid
              ORDER BY c.code'
        );
        $stmt->execute([':lid' => $this->id]);
        return array_map(fn(array $row) => Course::fromRow($row), $stmt->fetchAll());
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int) $row['id'],
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
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'full_name'     => $this->getFullName(),
            'email'         => $this->email,
            'department_id' => $this->departmentId,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
            'role'          => $this->getRole(),
        ];
    }
}

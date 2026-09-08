<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents an academic department.
 *
 * Encapsulates all database operations for the `departments` table and
 * provides convenience methods to retrieve related Courses and Lecturers.
 */
class Department
{
    public function __construct(
        public ?int             $id          = null,
        public string           $name        = '',
        public string           $code        = '',
        public ?string          $description = null,
        public ?string          $createdAt   = null,
        public ?string          $updatedAt   = null,
    ) {}

    // ------------------------------------------------------------------ //
    //  Static finders
    // ------------------------------------------------------------------ //

    /**
     * Return all departments ordered by name.
     *
     * @return Department[]
     */
    public static function findAll(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM departments ORDER BY name');
        return array_map(
            fn(array $row) => self::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /**
     * Find a single department by primary key, or null if not found.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    // ------------------------------------------------------------------ //
    //  Write operations
    // ------------------------------------------------------------------ //

    /**
     * Persist a new department row and return the populated instance.
     *
     * @param array{name: string, code: string, description?: string|null} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO departments (name, code, description) VALUES (:name, :code, :description)'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':code'        => $data['code'],
            ':description' => $data['description'] ?? null,
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update this department's mutable fields and persist them.
     *
     * @param array{name?: string, code?: string, description?: string|null} $data
     */
    public function update(array $data): self
    {
        $this->name        = $data['name']        ?? $this->name;
        $this->code        = $data['code']         ?? $this->code;
        $this->description = array_key_exists('description', $data)
            ? $data['description']
            : $this->description;

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE departments
                SET name = :name, code = :code, description = :description
              WHERE id   = :id'
        );
        $stmt->execute([
            ':name'        => $this->name,
            ':code'        => $this->code,
            ':description' => $this->description,
            ':id'          => $this->id,
        ]);
        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Relationship accessors
    // ------------------------------------------------------------------ //

    /**
     * Return all courses belonging to this department.
     *
     * @return Course[]
     */
    public function getCourses(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM courses WHERE department_id = :id ORDER BY code'
        );
        $stmt->execute([':id' => $this->id]);
        return array_map(
            fn(array $row) => Course::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /**
     * Return all lecturers associated with this department.
     *
     * @return Lecturer[]
     */
    public function getLecturers(): array
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM lecturers WHERE department_id = :id ORDER BY last_name, first_name'
        );
        $stmt->execute([':id' => $this->id]);
        return array_map(
            fn(array $row) => Lecturer::fromRow($row),
            $stmt->fetchAll()
        );
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    public static function fromRow(array $row): self
    {
        return new self(
            id:          (int) $row['id'],
            name:              $row['name'],
            code:              $row['code'],
            description:       $row['description'] ?? null,
            createdAt:         $row['created_at']  ?? null,
            updatedAt:         $row['updated_at']  ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}

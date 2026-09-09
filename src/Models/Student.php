<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use App\Interfaces\SearchableInterface;
use PDO;

/**
 * Represents a student within the academic system.
 * Inherits from abstract User base class.
 *
 * Encapsulates database operations for the `students` table and provides
 * relationship accessors for the student's Department, Address, and Enrollments.
 */
class Student extends User implements SearchableInterface
{
    public function __construct(
        public ?int             $id           = null,
        public string           $studentId    = '',
        public string           $firstName    = '',
        public string           $lastName     = '',
        public string           $email        = '',
        public ?int             $departmentId = null,
        public ?string          $createdAt    = null,
        public ?string          $updatedAt    = null,
    ) {
        parent::__construct(
            id: $id,
            username: $studentId,
            email: $email,
            passwordHash: '',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // ------------------------------------------------------------------ //
    //  Polymorphic role implementation
    // ------------------------------------------------------------------ //

    public function getRole(): string
    {
        return 'student';
    }

    // ------------------------------------------------------------------ //
    //  SearchableInterface implementation
    // ------------------------------------------------------------------ //

    public function getSearchTitle(): string
    {
        return "{$this->getFullName()} ({$this->studentId})";
    }

    public function getSearchSubtitle(): string
    {
        return $this->email;
    }

    public function getSearchUrl(): string
    {
        return "/students/{$this->id}";
    }

    // ------------------------------------------------------------------ //
    //  Computed properties
    // ------------------------------------------------------------------ //

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    // ------------------------------------------------------------------ //
    //  Static finders & search
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

    /**
     * Search students by name (first name, last name, or full name).
     *
     * @return Student[]
     */
    public static function searchByName(string $nameQuery): array
    {
        $term = '%' . trim($nameQuery) . '%';
        $pdo  = Connection::getInstance();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $concat = $driver === 'sqlite' ? "(first_name || ' ' || last_name)" : "CONCAT(first_name, ' ', last_name)";

        $stmt = $pdo->prepare(
            "SELECT * FROM students
              WHERE first_name LIKE :t1
                 OR last_name  LIKE :t2
                 OR {$concat} LIKE :t3
              ORDER BY last_name, first_name"
        );
        $stmt->execute([':t1' => $term, ':t2' => $term, ':t3' => $term]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Search students by registration ID or Name.
     *
     * @return Student[]
     */
    public static function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return self::findAll();
        }

        $term = '%' . $query . '%';
        $pdo  = Connection::getInstance();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $concat = $driver === 'sqlite' ? "(first_name || ' ' || last_name)" : "CONCAT(first_name, ' ', last_name)";

        $stmt = $pdo->prepare(
            "SELECT * FROM students
              WHERE student_id LIKE :t1
                 OR first_name LIKE :t2
                 OR last_name  LIKE :t3
                 OR {$concat} LIKE :t4
                 OR email LIKE :t5
              ORDER BY last_name, first_name"
        );
        $stmt->execute([
            ':t1' => $term,
            ':t2' => $term,
            ':t3' => $term,
            ':t4' => $term,
            ':t5' => $term,
        ]);
        return array_map(fn(array $row) => self::fromRow($row), $stmt->fetchAll());
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
            ':student_id'    => trim($data['student_id']),
            ':first_name'    => trim($data['first_name']),
            ':last_name'     => trim($data['last_name']),
            ':email'         => trim($data['email']),
            ':department_id' => !empty($data['department_id']) ? (int) $data['department_id'] : null,
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
        $this->studentId    = isset($data['student_id']) ? trim($data['student_id']) : $this->studentId;
        $this->firstName    = isset($data['first_name']) ? trim($data['first_name']) : $this->firstName;
        $this->lastName     = isset($data['last_name'])  ? trim($data['last_name'])  : $this->lastName;
        $this->email        = isset($data['email'])      ? trim($data['email'])      : $this->email;
        $this->departmentId = array_key_exists('department_id', $data)
            ? (!empty($data['department_id']) ? (int) $data['department_id'] : null)
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
     * Return associated address, or null if none exists yet.
     */
    public function getAddress(): ?Address
    {
        return $this->id !== null ? Address::findByStudentId($this->id) : null;
    }

    /**
     * Create or update address for this student.
     *
     * @param array{province: string, district: string, sector: string, cell: string} $addressData
     */
    public function saveAddress(array $addressData): Address
    {
        $existing = $this->getAddress();
        if ($existing !== null) {
            return $existing->update($addressData);
        }

        return Address::create(array_merge($addressData, ['student_id' => $this->id]));
    }

    /**
     * Return all enrollments for this student.
     *
     * @return Enrollment[]
     */
    public function getEnrollments(bool $activeOnly = false): array
    {
        return $this->id !== null ? Enrollment::findByStudent($this->id, $activeOnly) : [];
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

    /**
     * Return the student's academic record, or null if not yet created.
     */
    public function getAcademicRecord(): ?AcademicRecord
    {
        return $this->id !== null ? AcademicRecord::findByStudentId($this->id) : null;
    }

    /**
     * Return all academic grades for this student.
     *
     * @return Grade[]
     */
    public function getGrades(): array
    {
        return $this->id !== null ? Grade::findByStudent($this->id) : [];
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
            departmentId: isset($row['department_id']) && $row['department_id'] !== null ? (int) $row['department_id'] : null,
            createdAt:          $row['created_at']   ?? null,
            updatedAt:          $row['updated_at']   ?? null,
        );
    }

    public function toArray(): array
    {
        $address = $this->getAddress();
        return [
            'id'            => $this->id,
            'student_id'    => $this->studentId,
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'full_name'     => $this->getFullName(),
            'email'         => $this->email,
            'department_id' => $this->departmentId,
            'address'       => $address ? $address->toArray() : null,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}

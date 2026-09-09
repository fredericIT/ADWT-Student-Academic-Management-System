<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use App\Interfaces\AuthenticatableInterface;
use PDO;

/**
 * Abstract base class representing a user within the SAMS system.
 *
 * Encapsulates common user identity, authentication state, and defines
 * polymorphic role behavior for subclasses (Administrator, Student, Lecturer).
 */
abstract class User implements AuthenticatableInterface
{
    public function __construct(
        protected ?int    $id           = null,
        protected string  $username     = '',
        protected string  $email        = '',
        protected string  $passwordHash = '',
        protected ?string $createdAt    = null,
        protected ?string $updatedAt    = null,
    ) {}

    /**
     * Destructor demonstration for object cleanup / session logging.
     */
    public function __destruct()
    {
        // Demonstrates object destruction lifecycle
    }

    // ------------------------------------------------------------------ //
    //  Polymorphic role requirement
    // ------------------------------------------------------------------ //

    /**
     * Return the specific system role ('administrator', 'lecturer', 'student').
     */
    abstract public function getRole(): string;

    // ------------------------------------------------------------------ //
    //  Encapsulation Getters & Setters
    // ------------------------------------------------------------------ //

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = trim($username);
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = trim($email);
        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPassword(string $plainPassword): static
    {
        $this->passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
        return $this;
    }

    public function setPasswordHash(string $hash): static
    {
        $this->passwordHash = $hash;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    // ------------------------------------------------------------------ //
    //  Authentication Logic
    // ------------------------------------------------------------------ //

    public function verifyPassword(string $password): bool
    {
        if ($this->passwordHash === '') {
            return false;
        }
        return password_verify($password, $this->passwordHash);
    }

    // ------------------------------------------------------------------ //
    //  Database Finders & Mapping
    // ------------------------------------------------------------------ //

    /**
     * Locate a user account by unique username or email.
     */
    public static function findByCredentials(string $usernameOrEmail): ?array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM users WHERE LOWER(username) = LOWER(:val) OR LOWER(email) = LOWER(:val) LIMIT 1'
        );
        $stmt->execute([':val' => trim($usernameOrEmail)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Instantiate the proper polymorphic subclass from a database row.
     */
    public static function createFromRow(array $row): ?User
    {
        $role = strtolower((string) ($row['role'] ?? ''));

        return match ($role) {
            'administrator' => new Administrator(
                id: (int) $row['id'],
                username: (string) $row['username'],
                email: (string) $row['email'],
                passwordHash: (string) $row['password_hash'],
                createdAt: $row['created_at'] ?? null,
                updatedAt: $row['updated_at'] ?? null,
            ),
            'lecturer' => !empty($row['lecturer_id'])
                ? Lecturer::findById((int) $row['lecturer_id'])
                : new Lecturer(
                    id: (int) ($row['lecturer_id'] ?? $row['id']),
                    firstName: (string) $row['username'],
                    lastName: '',
                    email: (string) $row['email']
                ),
            'student' => !empty($row['student_id'])
                ? Student::findById((int) $row['student_id'])
                : new Student(
                    id: (int) ($row['student_id'] ?? $row['id']),
                    studentId: (string) $row['username'],
                    firstName: (string) $row['username'],
                    lastName: '',
                    email: (string) $row['email']
                ),
            default => null,
        };
    }
}

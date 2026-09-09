<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Exceptions\AuthenticationException;
use App\Models\Administrator;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use PDO;

/**
 * Service responsible for user authentication.
 */
class AuthService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    /**
     * Authenticate a user with email and password.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws AuthenticationException
     */
    public function authenticate(string $email, string $password): User
    {
        $trimmedEmail = trim($email);

        if ($trimmedEmail === '' || $password === '') {
            throw AuthenticationException::missingCredentials();
        }

        $stmt = $this->pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute([':email' => $trimmedEmail]);
        $userRow = $stmt->fetch();

        if (!$userRow) {
            throw AuthenticationException::invalidCredentials();
        }

        if (!password_verify($password, $userRow['password_hash'])) {
            throw AuthenticationException::invalidCredentials();
        }

        return $this->createUserInstance($userRow);
    }

    /**
     * Instantiate the concrete User subclass based on database role.
     *
     * @param array<string, mixed> $userRow
     * @return User
     * @throws AuthenticationException
     */
    private function createUserInstance(array $userRow): User
    {
        $userId = (int) $userRow['id'];
        $role   = strtolower((string) $userRow['role']);

        switch ($role) {
            case 'student':
                return new Student(
                    userId: $userId,
                    name: (string) $userRow['name'],
                    email: (string) $userRow['email'],
                    password: (string) $userRow['password_hash']
                );

            case 'lecturer':
                return $this->createLecturerUser($userRow);

            case 'administrator':
                return new Administrator(
                    userId: $userId,
                    name: (string) $userRow['name'],
                    email: (string) $userRow['email'],
                    password: (string) $userRow['password_hash']
                );

            default:
                throw AuthenticationException::unsupportedRole((string) $userRow['role']);
        }
    }

    /**
     * Create a Lecturer instance, linking to lecturers table details if present.
     *
     * @param array<string, mixed> $userRow
     * @return Lecturer
     */
    private function createLecturerUser(array $userRow): Lecturer
    {
        $userId = (int) $userRow['id'];
        $email  = (string) $userRow['email'];

        $stmt = $this->pdo->prepare('SELECT * FROM lecturers WHERE user_id = :user_id OR LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute([
            ':user_id' => $userId,
            ':email'   => strtolower($email),
        ]);
        $lecturerRow = $stmt->fetch();

        if ($lecturerRow) {
            return new Lecturer(
                id: $userId,
                firstName: (string) $lecturerRow['first_name'],
                lastName: (string) $lecturerRow['last_name'],
                email: $email,
                departmentId: isset($lecturerRow['department_id']) ? (int) $lecturerRow['department_id'] : null,
                createdAt: isset($lecturerRow['created_at']) ? (string) $lecturerRow['created_at'] : null,
                updatedAt: isset($lecturerRow['updated_at']) ? (string) $lecturerRow['updated_at'] : null,
                lecturerId: (string) $lecturerRow['id'],
                password: (string) $userRow['password_hash']
            );
        }

        $nameParts = explode(' ', trim((string) $userRow['name']), 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? '';

        return new Lecturer(
            id: $userId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            password: (string) $userRow['password_hash']
        );
    }
}

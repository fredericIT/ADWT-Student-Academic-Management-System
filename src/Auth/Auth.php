<?php

declare(strict_types=1);

namespace App\Auth;

use App\Database\Connection;
use App\Models\Administrator;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use PDO;

/**
 * Authentication and Role-Based Authorization Manager.
 *
 * Provides real database-backed authentication using bcrypt password hashes,
 * session management, role enforcement, and seamless role switching for UI testing.
 */
class Auth
{
    public const ROLE_ADMIN    = 'administrator';
    public const ROLE_LECTURER = 'lecturer';
    public const ROLE_STUDENT  = 'student';

    private static ?User $cachedUser = null;

    /**
     * Start session safely if not already active.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Attempt login with username/email and plaintext password.
     */
    public static function attempt(string $usernameOrEmail, string $password): bool
    {
        self::startSession();

        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM users WHERE LOWER(username) = LOWER(:val) OR LOWER(email) = LOWER(:val) LIMIT 1'
        );
        $stmt->execute([':val' => trim($usernameOrEmail)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        if (!password_verify($password, (string) $row['password_hash'])) {
            return false;
        }

        // Login successful: populate session
        $_SESSION['user_id']     = (int) $row['id'];
        $_SESSION['username']    = (string) $row['username'];
        $_SESSION['user_email']   = (string) $row['email'];
        $_SESSION['user_role']   = (string) $row['role'];
        $_SESSION['student_id']  = $row['student_id'] ? (int) $row['student_id'] : null;
        $_SESSION['lecturer_id'] = $row['lecturer_id'] ? (int) $row['lecturer_id'] : null;

        self::$cachedUser = null;
        return true;
    }

    /**
     * Log in a given User object directly.
     */
    public static function login(User $user): void
    {
        self::startSession();
        $_SESSION['user_id']   = $user->getId();
        $_SESSION['username']  = $user->getUsername();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();

        if ($user instanceof Student) {
            $_SESSION['student_id'] = $user->id;
        } elseif ($user instanceof Lecturer) {
            $_SESSION['lecturer_id'] = $user->id;
        }

        self::$cachedUser = $user;
    }

    /**
     * Terminate the authenticated session.
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
        self::$cachedUser = null;
    }

    /**
     * Check whether a user is currently authenticated.
     */
    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION['user_id']) || !empty($_SESSION['user_role']);
    }

    /**
     * Retrieve the currently authenticated User model instance.
     */
    public static function user(): ?User
    {
        self::startSession();
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $pdo = Connection::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                self::$cachedUser = User::createFromRow($row);
                return self::$cachedUser;
            }
        }

        // Fallback for role switcher session mode without explicit user_id
        $role = self::getRole();
        if ($role === self::ROLE_ADMIN) {
            self::$cachedUser = new Administrator(id: 1, username: 'admin', email: 'admin@adwt.ac.rw');
        } elseif ($role === self::ROLE_STUDENT) {
            self::$cachedUser = Student::findById(1) ?? new Student(id: 1, studentId: 'ST001', firstName: 'Eric', lastName: 'Habimana', email: 'eric.habimana@student.adwt.ac.rw');
        } elseif ($role === self::ROLE_LECTURER) {
            self::$cachedUser = Lecturer::findById(1) ?? new Lecturer(id: 1, firstName: 'Jean', lastName: 'Mugisha', email: 'jean.mugisha@adwt.ac.rw');
        }

        return self::$cachedUser;
    }

    /**
     * Get the current active user role.
     */
    public static function getRole(): string
    {
        self::startSession();
        return $_SESSION['user_role'] ?? self::ROLE_ADMIN;
    }

    /**
     * Set the current active user role (used for UI test switcher).
     */
    public static function setRole(string $role): void
    {
        self::startSession();
        $role = strtolower(trim($role));
        if (in_array($role, [self::ROLE_ADMIN, self::ROLE_LECTURER, self::ROLE_STUDENT], true)) {
            $_SESSION['user_role'] = $role;

            // Associate with matching seed account if user_id not already set
            $pdo = Connection::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE role = :role LIMIT 1');
            $stmt->execute([':role' => $role]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userRow) {
                $_SESSION['user_id']     = (int) $userRow['id'];
                $_SESSION['username']    = (string) $userRow['username'];
                $_SESSION['user_email']   = (string) $userRow['email'];
                $_SESSION['student_id']  = $userRow['student_id'] ? (int) $userRow['student_id'] : null;
                $_SESSION['lecturer_id'] = $userRow['lecturer_id'] ? (int) $userRow['lecturer_id'] : null;
            }

            self::$cachedUser = null;
        }
    }

    public static function isAdmin(): bool
    {
        return self::getRole() === self::ROLE_ADMIN;
    }

    public static function isLecturer(): bool
    {
        return self::getRole() === self::ROLE_LECTURER;
    }

    public static function isStudent(): bool
    {
        return self::getRole() === self::ROLE_STUDENT;
    }

    /**
     * Return the student ID if logged in as a student.
     */
    public static function getStudentId(): ?int
    {
        self::startSession();
        return $_SESSION['student_id'] ?? null;
    }

    /**
     * Return the lecturer ID if logged in as a lecturer.
     */
    public static function getLecturerId(): ?int
    {
        self::startSession();
        return $_SESSION['lecturer_id'] ?? null;
    }

    /**
     * Check if current role has permission to perform an action.
     */
    public static function can(string $action): bool
    {
        $role = self::getRole();

        // Administrator has access to all actions
        if ($role === self::ROLE_ADMIN) {
            return true;
        }

        return match ($action) {
            'view_dashboard', 'search'                     => true,
            'view_courses', 'view_course_students'         => true,
            'view_students'                                => in_array($role, [self::ROLE_ADMIN, self::ROLE_LECTURER], true),
            'manage_students', 'manage_departments',
            'manage_courses', 'manage_lecturers'           => $role === self::ROLE_ADMIN,
            'enroll_course', 'drop_course'                 => in_array($role, [self::ROLE_ADMIN, self::ROLE_STUDENT], true),
            'record_marks'                                 => in_array($role, [self::ROLE_ADMIN, self::ROLE_LECTURER], true),
            default                                        => false,
        };
    }

    /**
     * Return list of available roles with human-readable labels.
     *
     * @return array<string, string>
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_ADMIN    => 'Administrator',
            self::ROLE_LECTURER => 'Lecturer',
            self::ROLE_STUDENT  => 'Student',
        ];
    }
}

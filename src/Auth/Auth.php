<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * Lightweight role-based authentication & authorization manager.
 *
 * Provides session-backed role management (Administrator, Lecturer, Student)
 * with permission checks and role switching for UI testing and system integration.
 */
class Auth
{
    public const ROLE_ADMIN    = 'administrator';
    public const ROLE_LECTURER = 'lecturer';
    public const ROLE_STUDENT  = 'student';

    /**
     * Get the current active user role.
     */
    public static function getRole(): string
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        return $_SESSION['user_role'] ?? self::ROLE_ADMIN;
    }

    /**
     * Set the current active user role.
     */
    public static function setRole(string $role): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $role = strtolower(trim($role));
        if (in_array($role, [self::ROLE_ADMIN, self::ROLE_LECTURER, self::ROLE_STUDENT], true)) {
            $_SESSION['user_role'] = $role;
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

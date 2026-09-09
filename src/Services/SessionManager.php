<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Service responsible for managing user session state (login, logout, active user info).
 */
class SessionManager
{
    /**
     * Start PHP session safely if not already active.
     */
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Log an authenticated User into the session and regenerate session ID.
     *
     * @param User $user
     */
    public function login(User $user): void
    {
        $this->startSession();

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id'    => $user->getUserId(),
            'role'  => $user->getRole(),
            'name'  => $user->getName(),
            'email' => $user->getEmail(),
        ];
    }

    /**
     * Check if a user is currently authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        $this->startSession();
        return isset($_SESSION['user']['id']) && $_SESSION['user']['id'] !== null;
    }

    /**
     * Alias for isAuthenticated().
     */
    public function isLoggedIn(): bool
    {
        return $this->isAuthenticated();
    }

    /**
     * Get the authenticated user's ID, or null if not logged in.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return (int) $_SESSION['user']['id'];
    }

    /**
     * Get the authenticated user's role, or null if not logged in.
     *
     * @return string|null
     */
    public function getUserRole(): ?string
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return (string) $_SESSION['user']['role'];
    }

    /**
     * Get the authenticated user session details array, or null if not logged in.
     *
     * @return array{id: int, role: string, name: string, email: string}|null
     */
    public function getUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $_SESSION['user'];
    }

    /**
     * Log out the current user and safely clear/destroy the session.
     */
    public function logout(): void
    {
        $this->startSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
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
            session_destroy();
        }
    }

    /**
     * Alias for logout().
     */
    public function destroy(): void
    {
        $this->logout();
    }
}

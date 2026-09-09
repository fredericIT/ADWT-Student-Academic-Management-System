<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuthorizationException;

/**
 * Service responsible for role-based authorization logic.
 */
class AuthorizationService
{
    private SessionManager $sessionManager;

    public function __construct(?SessionManager $sessionManager = null)
    {
        $this->sessionManager = $sessionManager ?? new SessionManager();
    }

    /**
     * Check if a user is currently authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->sessionManager->isAuthenticated();
    }

    /**
     * Check if the authenticated user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        $currentRole = $this->sessionManager->getUserRole();

        if ($currentRole === null) {
            return false;
        }

        return strtolower($currentRole) === strtolower(trim($role));
    }

    /**
     * Check if the authenticated user has any of the specified roles.
     *
     * @param string[] $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        $currentRole = $this->sessionManager->getUserRole();

        if ($currentRole === null) {
            return false;
        }

        $normalizedCurrentRole = strtolower($currentRole);

        foreach ($roles as $role) {
            if ($normalizedCurrentRole === strtolower(trim((string) $role))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Require authentication; throws AuthorizationException if unauthenticated.
     *
     * @throws AuthorizationException
     */
    public function requireAuthentication(): void
    {
        if (!$this->isAuthenticated()) {
            throw AuthorizationException::unauthenticated();
        }
    }

    /**
     * Require a specific role; throws AuthorizationException if unauthenticated or unauthorized.
     *
     * @param string $role
     * @throws AuthorizationException
     */
    public function requireRole(string $role): void
    {
        $this->requireAuthentication();

        if (!$this->hasRole($role)) {
            throw AuthorizationException::forbidden();
        }
    }

    /**
     * Require any of the specified roles; throws AuthorizationException if unauthenticated or unauthorized.
     *
     * @param string[] $roles
     * @throws AuthorizationException
     */
    public function requireAnyRole(array $roles): void
    {
        $this->requireAuthentication();

        if (!$this->hasAnyRole($roles)) {
            throw AuthorizationException::forbidden();
        }
    }
}

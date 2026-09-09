<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface defining authentication contract for user entities.
 */
interface AuthenticatableInterface
{
    /**
     * Get the unique username or identifier.
     */
    public function getUsername(): string;

    /**
     * Get the email address.
     */
    public function getEmail(): string;

    /**
     * Get the role name for authorization.
     */
    public function getRole(): string;

    /**
     * Verify a plaintext password against the stored password hash.
     */
    public function verifyPassword(string $password): bool;
}

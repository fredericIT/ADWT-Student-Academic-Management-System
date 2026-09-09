<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown for authentication failures.
 */
class AuthenticationException extends Exception
{
    public static function missingCredentials(): self
    {
        return new self('Email and password are required.');
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid email or password.');
    }

    public static function unsupportedRole(string $role): self
    {
        return new self("Unsupported user role: {$role}.");
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when authorization checks fail.
 */
class AuthorizationException extends Exception
{
    public static function unauthenticated(): self
    {
        return new self('Authentication required.');
    }

    public static function forbidden(?string $message = null): self
    {
        return new self($message ?? 'Access denied.');
    }
}

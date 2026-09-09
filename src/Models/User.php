<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Abstract base class representing a system user.
 */
abstract class User
{
    public function __construct(
        protected int $userId = 0,
        protected string $name = '',
        protected string $email = '',
        protected string $password = '',
    ) {}

    /**
     * Minimal login stub for architectural foundation.
     */
    public function login(): bool
    {
        return true;
    }

    /**
     * Minimal logout stub for architectural foundation.
     */
    public function logout(): void
    {
        // Session / authentication cleanup stub
    }

    /**
     * Return the specific user role.
     */
    abstract public function getRole(): string;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}

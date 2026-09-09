<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Represents a system administrator with elevated privileges.
 * Inherits from abstract User base class.
 */
class Administrator extends User
{
    public function getRole(): string
    {
        return 'administrator';
    }

    public function getFullName(): string
    {
        return $this->username !== '' ? ucfirst($this->username) : 'System Administrator';
    }

    public function isAdministrator(): bool
    {
        return true;
    }
}

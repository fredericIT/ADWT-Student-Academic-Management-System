<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Represents a system administrator within the academic management system.
 */
class Administrator extends User
{
    public function manageStudent(): bool
    {
        return true;
    }

    public function manageCourse(): bool
    {
        return true;
    }

    public function manageDepartment(): bool
    {
        return true;
    }

    public function manageLecturer(): bool
    {
        return true;
    }

    public function getRole(): string
    {
        return 'Administrator';
    }
}

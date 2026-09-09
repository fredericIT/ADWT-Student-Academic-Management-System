<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Represents a student within the academic management system.
 */
class Student extends User
{
    private string $studentId;
    private ?string $dateOfBirth;
    private string $programme;

    public function __construct(
        int $userId = 0,
        string $name = '',
        string $email = '',
        string $password = '',
        string $studentId = '',
        ?string $dateOfBirth = null,
        string $programme = '',
    ) {
        parent::__construct(
            userId: $userId,
            name: $name,
            email: $email,
            password: $password
        );
        $this->studentId   = $studentId;
        $this->dateOfBirth = $dateOfBirth;
        $this->programme   = $programme;
    }

    public function registerCourse(): bool
    {
        return true;
    }

    public function dropCourse(): bool
    {
        return true;
    }

    public function viewRegisteredCourses(): array
    {
        return [];
    }

    public function updateProfile(): bool
    {
        return true;
    }

    public function getProfile(): array
    {
        return [
            'user_id'       => $this->getUserId(),
            'name'          => $this->getName(),
            'email'         => $this->getEmail(),
            'student_id'    => $this->studentId,
            'date_of_birth' => $this->dateOfBirth,
            'programme'     => $this->programme,
            'role'          => $this->getRole(),
        ];
    }

    public function getRole(): string
    {
        return 'Student';
    }

    public function getStudentId(): string
    {
        return $this->studentId;
    }

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function getProgramme(): string
    {
        return $this->programme;
    }
}

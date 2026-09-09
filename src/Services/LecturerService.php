<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Department;
use App\Validator;
use App\Database\Connection;
use InvalidArgumentException;

/**
 * Business logic layer for Lecturer management.
 */
class LecturerService
{
    /**
     * Register (create) a new lecturer after validating input.
     *
     * @param array{first_name: mixed, last_name: mixed, email: mixed, department_id?: mixed} $data
     * @throws InvalidArgumentException
     */
    public function register(array $data): Lecturer
    {
        [$firstName, $lastName, $email] = $this->validate($data);

        $this->assertUniqueEmail($email);

        return Lecturer::create([
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'email'         => $email,
            'department_id' => isset($data['department_id']) && $data['department_id'] !== ''
                ? (int) $data['department_id']
                : null,
        ]);
    }

    /**
     * Update an existing lecturer's details.
     *
     * @throws InvalidArgumentException
     */
    public function update(Lecturer $lecturer, array $data): Lecturer
    {
        [$firstName, $lastName, $email] = $this->validate($data, $lecturer);

        if (strtolower($email) !== strtolower($lecturer->email)) {
            $this->assertUniqueEmail($email, $lecturer->id);
        }

        return $lecturer->update([
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'email'         => $email,
            'department_id' => array_key_exists('department_id', $data)
                ? (isset($data['department_id']) && $data['department_id'] !== '' ? (int) $data['department_id'] : null)
                : $lecturer->departmentId,
        ]);
    }

    /**
     * Associate a lecturer with a department (or disassociate with null).
     *
     * @throws InvalidArgumentException
     */
    public function associateDepartment(Lecturer $lecturer, ?int $departmentId): void
    {
        if ($departmentId !== null && !Department::findById($departmentId)) {
            throw new InvalidArgumentException("Department with ID {$departmentId} not found.");
        }
        $lecturer->associateDepartment($departmentId);
    }

    // ------------------------------------------------------------------ //
    //  Query helpers
    // ------------------------------------------------------------------ //

    public function getAll(): array
    {
        return Lecturer::findAll();
    }

    public function getById(int $id): ?Lecturer
    {
        return Lecturer::findById($id);
    }

    public function delete(Lecturer $lecturer): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('DELETE FROM lecturers WHERE id = :id');
        return $stmt->execute([':id' => $lecturer->id]);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers
    // ------------------------------------------------------------------ //

    /**
     * @return array{string, string, string}  [$firstName, $lastName, $email]
     */
    private function validate(array $data, ?Lecturer $existing = null): array
    {
        $firstName = isset($data['first_name']) ? trim((string) $data['first_name']) : ($existing?->firstName ?? '');
        $lastName  = isset($data['last_name'])  ? trim((string) $data['last_name'])  : ($existing?->lastName  ?? '');
        $email     = isset($data['email'])       ? trim((string) $data['email'])      : ($existing?->email     ?? '');

        $errors = Validator::all([
            fn() => Validator::required($firstName, 'First name'),
            fn() => Validator::maxLength($firstName, 100, 'First name'),
            fn() => Validator::required($lastName, 'Last name'),
            fn() => Validator::maxLength($lastName, 100, 'Last name'),
            fn() => Validator::required($email, 'Email'),
            fn() => Validator::email($email, 'Email'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return [$firstName, $lastName, $email];
    }

    private function assertUniqueEmail(string $email, ?int $excludeId = null): void
    {
        $pdo = Connection::getInstance();
        $sql = 'SELECT id FROM lecturers WHERE LOWER(email) = LOWER(:email)';
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
        }
        $stmt   = $pdo->prepare($sql);
        $params = [':email' => $email];
        if ($excludeId !== null) {
            $params[':id'] = $excludeId;
        }
        $stmt->execute($params);

        if ($stmt->fetch()) {
            throw new InvalidArgumentException("A lecturer with email \"{$email}\" already exists.");
        }
    }
}

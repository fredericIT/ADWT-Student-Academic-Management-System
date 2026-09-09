<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Validator;
use App\Database\Connection;
use InvalidArgumentException;
use PDOException;

/**
 * Business logic layer for Department management.
 * Validates input, enforces uniqueness constraints, and delegates
 * persistence to the Department model.
 */
class DepartmentService
{
    /**
     * Create a new department after validating input.
     *
     * @param array{name: mixed, code: mixed, description?: mixed} $data
     * @throws InvalidArgumentException on validation failure or duplicate name/code.
     */
    public function create(array $data): Department
    {
        $errors = Validator::all([
            fn() => Validator::required($data['name'] ?? '', 'Name'),
            fn() => Validator::minLength($data['name'] ?? '', 3, 'Name'),
            fn() => Validator::maxLength($data['name'] ?? '', 255, 'Name'),
            fn() => Validator::required($data['code'] ?? '', 'Code'),
            fn() => Validator::code($data['code'] ?? '', 'Code'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        $cleaned = [
            'name'        => trim((string) $data['name']),
            'code'        => Validator::code($data['code'], 'Code'),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
        ];

        $this->assertUniqueNameAndCode($cleaned['name'], $cleaned['code']);

        return Department::create($cleaned);
    }

    /**
     * Update an existing department.
     *
     * @param array{name?: mixed, code?: mixed, description?: mixed} $data
     * @throws InvalidArgumentException
     */
    public function update(Department $department, array $data): Department
    {
        $name = isset($data['name']) ? trim((string) $data['name']) : $department->name;
        $code = isset($data['code']) ? strtoupper(trim((string) $data['code'])) : $department->code;

        $errors = Validator::all([
            fn() => Validator::required($name, 'Name'),
            fn() => Validator::minLength($name, 3, 'Name'),
            fn() => Validator::maxLength($name, 255, 'Name'),
            fn() => Validator::code($code, 'Code'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        // Uniqueness checks — exclude the current record
        $this->assertUniqueNameAndCode($name, $code, $department->id);

        return $department->update([
            'name'        => $name,
            'code'        => $code,
            'description' => isset($data['description']) ? trim((string) $data['description']) : $department->description,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Query helpers
    // ------------------------------------------------------------------ //

    public function getAll(): array
    {
        return Department::findAll();
    }

    public function getById(int $id): ?Department
    {
        return Department::findById($id);
    }

    public function delete(Department $department): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('DELETE FROM departments WHERE id = :id');
        return $stmt->execute([':id' => $department->id]);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers
    // ------------------------------------------------------------------ //

    private function assertUniqueNameAndCode(string $name, string $code, ?int $excludeId = null): void
    {
        $pdo = Connection::getInstance();

        $nameSql  = 'SELECT id FROM departments WHERE LOWER(name) = LOWER(:name)';
        $codeSql  = 'SELECT id FROM departments WHERE UPPER(code) = UPPER(:code)';
        $params   = [];

        if ($excludeId !== null) {
            $nameSql .= ' AND id != :id';
            $codeSql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }

        $nameStmt = $pdo->prepare($nameSql);
        $nameStmt->execute(array_merge([':name' => $name], $params));
        if ($nameStmt->fetch()) {
            throw new InvalidArgumentException("A department with the name \"{$name}\" already exists.");
        }

        $codeStmt = $pdo->prepare($codeSql);
        $codeStmt->execute(array_merge([':code' => $code], $params));
        if ($codeStmt->fetch()) {
            throw new InvalidArgumentException("A department with the code \"{$code}\" already exists.");
        }
    }
}

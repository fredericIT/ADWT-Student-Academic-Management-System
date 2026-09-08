<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Models\Address;
use App\Models\Department;
use App\Validator;
use App\Database\Connection;
use InvalidArgumentException;
use Exception;

/**
 * Business logic layer for Student management.
 * Validates input, enforces duplicate student ID/email checks,
 * manages address integration, and handles database operations cleanly.
 */
class StudentService
{
    /**
     * Register a new student along with their address in a single database transaction.
     *
     * @param array{
     *     student_id: mixed,
     *     first_name: mixed,
     *     last_name: mixed,
     *     email: mixed,
     *     department_id?: mixed,
     *     province?: mixed,
     *     district?: mixed,
     *     sector?: mixed,
     *     cell?: mixed
     * } $data
     * @throws InvalidArgumentException on validation or duplicate errors.
     */
    public function registerStudent(array $data): Student
    {
        $studentId    = trim((string) ($data['student_id'] ?? ''));
        $firstName    = trim((string) ($data['first_name'] ?? ''));
        $lastName     = trim((string) ($data['last_name'] ?? ''));
        $email        = trim((string) ($data['email'] ?? ''));
        $departmentId = !empty($data['department_id']) ? (int) $data['department_id'] : null;

        $errors = Validator::all([
            fn() => Validator::required($studentId, 'Student ID'),
            fn() => Validator::minLength($studentId, 3, 'Student ID'),
            fn() => Validator::maxLength($studentId, 50, 'Student ID'),
            fn() => Validator::required($firstName, 'First Name'),
            fn() => Validator::minLength($firstName, 2, 'First Name'),
            fn() => Validator::maxLength($firstName, 100, 'First Name'),
            fn() => Validator::required($lastName, 'Last Name'),
            fn() => Validator::minLength($lastName, 2, 'Last Name'),
            fn() => Validator::maxLength($lastName, 100, 'Last Name'),
            fn() => Validator::required($email, 'Email'),
            fn() => Validator::email($email, 'Email'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        if ($departmentId !== null && Department::findById($departmentId) === null) {
            throw new InvalidArgumentException("Selected department (ID: {$departmentId}) does not exist.");
        }

        $this->assertUniqueStudentIdAndEmail($studentId, $email);

        $pdo = Connection::getInstance();
        $pdo->beginTransaction();

        try {
            $student = Student::create([
                'student_id'    => $studentId,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => $email,
                'department_id' => $departmentId,
            ]);

            $province = trim((string) ($data['province'] ?? ''));
            $district = trim((string) ($data['district'] ?? ''));
            $sector   = trim((string) ($data['sector'] ?? ''));
            $cell     = trim((string) ($data['cell'] ?? ''));

            if ($province !== '' || $district !== '' || $sector !== '' || $cell !== '') {
                Address::create([
                    'student_id' => $student->id,
                    'province'   => $province,
                    'district'   => $district,
                    'sector'     => $sector,
                    'cell'       => $cell,
                ]);
            }

            $pdo->commit();
            return $student;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update an existing student and their address details.
     *
     * @param array{
     *     student_id?: mixed,
     *     first_name?: mixed,
     *     last_name?: mixed,
     *     email?: mixed,
     *     department_id?: mixed,
     *     province?: mixed,
     *     district?: mixed,
     *     sector?: mixed,
     *     cell?: mixed
     * } $data
     * @throws InvalidArgumentException
     */
    public function updateStudent(Student $student, array $data): Student
    {
        $studentId    = isset($data['student_id']) ? trim((string) $data['student_id']) : $student->studentId;
        $firstName    = isset($data['first_name']) ? trim((string) $data['first_name']) : $student->firstName;
        $lastName     = isset($data['last_name'])  ? trim((string) $data['last_name'])  : $student->lastName;
        $email        = isset($data['email'])      ? trim((string) $data['email'])      : $student->email;
        $departmentId = array_key_exists('department_id', $data)
            ? (!empty($data['department_id']) ? (int) $data['department_id'] : null)
            : $student->departmentId;

        $errors = Validator::all([
            fn() => Validator::required($studentId, 'Student ID'),
            fn() => Validator::minLength($studentId, 3, 'Student ID'),
            fn() => Validator::required($firstName, 'First Name'),
            fn() => Validator::minLength($firstName, 2, 'First Name'),
            fn() => Validator::required($lastName, 'Last Name'),
            fn() => Validator::minLength($lastName, 2, 'Last Name'),
            fn() => Validator::required($email, 'Email'),
            fn() => Validator::email($email, 'Email'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        if ($departmentId !== null && Department::findById($departmentId) === null) {
            throw new InvalidArgumentException("Selected department (ID: {$departmentId}) does not exist.");
        }

        $this->assertUniqueStudentIdAndEmail($studentId, $email, $student->id);

        $pdo = Connection::getInstance();
        $pdo->beginTransaction();

        try {
            $student->update([
                'student_id'    => $studentId,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => $email,
                'department_id' => $departmentId,
            ]);

            if (isset($data['province']) || isset($data['district']) || isset($data['sector']) || isset($data['cell'])) {
                $student->saveAddress([
                    'province' => (string) ($data['province'] ?? ''),
                    'district' => (string) ($data['district'] ?? ''),
                    'sector'   => (string) ($data['sector'] ?? ''),
                    'cell'     => (string) ($data['cell'] ?? ''),
                ]);
            }

            $pdo->commit();
            return $student;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    // ------------------------------------------------------------------ //
    //  Query Methods
    // ------------------------------------------------------------------ //

    public function getAll(): array
    {
        return Student::findAll();
    }

    public function getById(int $id): ?Student
    {
        return Student::findById($id);
    }

    public function getByStudentId(string $studentId): ?Student
    {
        return Student::findByStudentId($studentId);
    }

    public function search(string $query): array
    {
        return Student::search($query);
    }

    // ------------------------------------------------------------------ //
    //  Private Helpers
    // ------------------------------------------------------------------ //

    private function assertUniqueStudentIdAndEmail(string $studentId, string $email, ?int $excludeId = null): void
    {
        $pdo = Connection::getInstance();

        $sidSql   = 'SELECT id FROM students WHERE UPPER(student_id) = UPPER(:sid)';
        $emailSql = 'SELECT id FROM students WHERE UPPER(email) = UPPER(:email)';
        $params   = [];

        if ($excludeId !== null) {
            $sidSql   .= ' AND id != :id';
            $emailSql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }

        $sidStmt = $pdo->prepare($sidSql);
        $sidStmt->execute(array_merge([':sid' => $studentId], $params));
        if ($sidStmt->fetch()) {
            throw new InvalidArgumentException("A student with Registration ID \"{$studentId}\" already exists.");
        }

        $emailStmt = $pdo->prepare($emailSql);
        $emailStmt->execute(array_merge([':email' => $email], $params));
        if ($emailStmt->fetch()) {
            throw new InvalidArgumentException("A student with Email address \"{$email}\" already exists.");
        }
    }
}

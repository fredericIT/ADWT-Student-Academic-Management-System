<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Lecturer;
use App\Validator;
use App\Database\Connection;
use App\Exceptions\CourseNotFoundException;
use InvalidArgumentException;

/**
 * Business logic layer for Course management.
 */
class CourseService
{
    /**
     * Create a new course after validating input.
     *
     * @param array{name: mixed, code: mixed, credits?: mixed, description?: mixed, department_id?: mixed} $data
     * @throws InvalidArgumentException
     */
    public function create(array $data): Course
    {
        [$name, $code, $credits] = $this->validate($data);

        $this->assertUniqueCode($code);

        return Course::create([
            'name'          => $name,
            'code'          => $code,
            'credits'       => $credits,
            'description'   => isset($data['description']) ? trim((string) $data['description']) : null,
            'department_id' => isset($data['department_id']) && $data['department_id'] !== ''
                ? (int) $data['department_id']
                : null,
        ]);
    }

    /**
     * Update an existing course.
     *
     * @throws InvalidArgumentException
     */
    public function update(Course $course, array $data): Course
    {
        [$name, $code, $credits] = $this->validate($data, $course);

        if (strtoupper($code) !== strtoupper($course->code)) {
            $this->assertUniqueCode($code, $course->id);
        }

        return $course->update([
            'name'          => $name,
            'code'          => $code,
            'credits'       => $credits,
            'description'   => isset($data['description']) ? trim((string) $data['description']) : $course->description,
            'department_id' => array_key_exists('department_id', $data)
                ? (isset($data['department_id']) && $data['department_id'] !== '' ? (int) $data['department_id'] : null)
                : $course->departmentId,
        ]);
    }

    /**
     * Assign a lecturer to a course; validates both exist.
     *
     * @throws InvalidArgumentException
     */
    public function assignLecturer(int $courseId, int $lecturerId): void
    {
        $course   = Course::findById($courseId);
        $lecturer = Lecturer::findById($lecturerId);

        if (!$course) {
            throw new CourseNotFoundException("Course with ID {$courseId} not found.");
        }
        if (!$lecturer) {
            throw new InvalidArgumentException("Lecturer with ID {$lecturerId} not found.");
        }

        $course->assignLecturer($lecturerId);
    }

    /**
     * Remove a lecturer from a course.
     *
     * @throws InvalidArgumentException
     */
    public function removeLecturer(int $courseId, int $lecturerId): void
    {
        $course = Course::findById($courseId);
        if (!$course) {
            throw new CourseNotFoundException("Course with ID {$courseId} not found.");
        }
        $course->removeLecturer($lecturerId);
    }

    // ------------------------------------------------------------------ //
    //  Query helpers
    // ------------------------------------------------------------------ //

    public function getAll(): array
    {
        return Course::findAll();
    }

    public function getById(int $id): ?Course
    {
        return Course::findById($id);
    }

    public function searchByCode(string $code): ?Course
    {
        return Course::findByCode($code);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers
    // ------------------------------------------------------------------ //

    /**
     * @return array{string, string, int}  [$name, $code, $credits]
     */
    private function validate(array $data, ?Course $existing = null): array
    {
        $name    = isset($data['name'])    ? trim((string) $data['name'])    : ($existing?->name    ?? '');
        $code    = isset($data['code'])    ? trim((string) $data['code'])    : ($existing?->code    ?? '');
        $credits = isset($data['credits']) ? (int) $data['credits']          : ($existing?->credits ?? 1);

        $errors = Validator::all([
            fn() => Validator::required($name, 'Name'),
            fn() => Validator::minLength($name, 2, 'Name'),
            fn() => Validator::maxLength($name, 255, 'Name'),
            fn() => Validator::required($code, 'Code'),
            fn() => Validator::code($code, 'Course code'),
            fn() => Validator::positiveInt($credits, 'Credits'),
        ]);

        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return [$name, strtoupper(trim($code)), $credits];
    }

    private function assertUniqueCode(string $code, ?int $excludeId = null): void
    {
        $pdo = Connection::getInstance();
        $sql = 'SELECT id FROM courses WHERE UPPER(code) = UPPER(:code)';
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
        }

        $stmt = $pdo->prepare($sql);
        $params = [':code' => $code];
        if ($excludeId !== null) {
            $params[':id'] = $excludeId;
        }
        $stmt->execute($params);

        if ($stmt->fetch()) {
            throw new InvalidArgumentException("Course code \"{$code}\" is already in use.");
        }
    }
}

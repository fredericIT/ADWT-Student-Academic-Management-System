<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Lightweight input validator.
 *
 * Each static method returns the (possibly sanitised) value on success
 * or throws InvalidArgumentException on failure, making it easy to collect
 * multiple errors by catching per field.
 */
class Validator
{
    /**
     * Ensure a value is non-empty after trimming.
     *
     * @throws InvalidArgumentException
     */
    public static function required(mixed $value, string $field): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            throw new InvalidArgumentException("{$field} is required.");
        }
        return $v;
    }

    /**
     * Validate an e-mail address.
     *
     * @throws InvalidArgumentException
     */
    public static function email(mixed $value, string $field = 'Email'): string
    {
        $v = trim((string) $value);
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("{$field} must be a valid email address.");
        }
        return $v;
    }

    /**
     * Ensure a string meets a minimum length (after trimming).
     *
     * @throws InvalidArgumentException
     */
    public static function minLength(mixed $value, int $min, string $field): string
    {
        $v = trim((string) $value);
        if (mb_strlen($v) < $min) {
            throw new InvalidArgumentException("{$field} must be at least {$min} characters long.");
        }
        return $v;
    }

    /**
     * Ensure a string does not exceed a maximum length (after trimming).
     *
     * @throws InvalidArgumentException
     */
    public static function maxLength(mixed $value, int $max, string $field): string
    {
        $v = trim((string) $value);
        if (mb_strlen($v) > $max) {
            throw new InvalidArgumentException("{$field} must not exceed {$max} characters.");
        }
        return $v;
    }

    /**
     * Ensure a value is a positive integer.
     *
     * @throws InvalidArgumentException
     */
    public static function positiveInt(mixed $value, string $field): int
    {
        $v = filter_var($value, FILTER_VALIDATE_INT);
        if ($v === false || $v < 1) {
            throw new InvalidArgumentException("{$field} must be a positive integer.");
        }
        return (int) $v;
    }

    /**
     * Ensure a value matches a simple alphanumeric + dash/underscore slug pattern
     * (suitable for course/department codes).
     *
     * @throws InvalidArgumentException
     */
    public static function code(mixed $value, string $field): string
    {
        $v = strtoupper(trim((string) $value));
        if (!preg_match('/^[A-Z0-9\-_]{2,20}$/', $v)) {
            throw new InvalidArgumentException(
                "{$field} must be 2–20 characters and contain only letters, digits, hyphens, or underscores."
            );
        }
        return $v;
    }

    /**
     * Collect multiple validation callables and return all error messages.
     *
     * @param  array<callable> $checks Array of callables; each should throw InvalidArgumentException on failure.
     * @return array<string>           Array of error messages (empty = all valid).
     */
    public static function all(array $checks): array
    {
        $errors = [];
        foreach ($checks as $check) {
            try {
                $check();
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }
        return $errors;
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an active enrollment already exists for a student in a course.
 */
class DuplicateEnrollmentException extends InvalidArgumentException
{
}

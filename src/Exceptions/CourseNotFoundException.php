<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an operation references a course that does not exist.
 */
class CourseNotFoundException extends InvalidArgumentException
{
}

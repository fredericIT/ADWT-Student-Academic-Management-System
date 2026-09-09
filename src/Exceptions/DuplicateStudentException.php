<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a duplicate student registration is attempted.
 */
class DuplicateStudentException extends InvalidArgumentException
{
}

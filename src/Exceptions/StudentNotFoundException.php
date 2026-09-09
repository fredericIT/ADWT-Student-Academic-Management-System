<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an operation is attempted on a non-existent student.
 */
class StudentNotFoundException extends InvalidArgumentException
{
}

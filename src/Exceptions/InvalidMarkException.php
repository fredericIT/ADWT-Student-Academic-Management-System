<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an academic mark falls outside the valid range (0.00 to 100.00).
 */
class InvalidMarkException extends InvalidArgumentException
{
}

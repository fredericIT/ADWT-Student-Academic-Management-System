<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an unauthorized user attempts an operation or modifications.
 */
class UnauthorizedActionException extends InvalidArgumentException
{
}

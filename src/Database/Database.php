<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Simple Database helper class.
 * Provides a clean, static connect() method returning the shared PDO instance.
 */
class Database
{
    public static function connect(): PDO
    {
        return Connection::getInstance();
    }
}

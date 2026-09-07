<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO connection singleton.
 *
 * Reads credentials from environment variables (set via .env loader in
 * public/index.php) or falls back to the constructor parameters.
 * Pass dsn='' to use an in-memory SQLite database (useful for unit tests).
 */
class Connection
{
    private static ?PDO $instance = null;

    /** Prevent direct instantiation. */
    private function __construct() {}

    /**
     * Return the shared PDO instance, creating it on first call.
     *
     * @param string|null $dsn      Full PDO DSN (overrides env vars when provided).
     * @param string|null $user     DB username (overrides env when provided).
     * @param string|null $password DB password (overrides env when provided).
     */
    public static function getInstance(
        ?string $dsn      = null,
        ?string $user     = null,
        ?string $password = null
    ): PDO {
        if (self::$instance === null) {
            $dsn      ??= self::buildDsnFromEnv();
            $user     ??= (string) ($_ENV['DB_USER'] ?? 'root');
            $password ??= (string) ($_ENV['DB_PASS'] ?? '');

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    'Database connection failed: ' . $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }

    /**
     * Replace the shared instance — used by tests to inject a SQLite PDO.
     */
    public static function setInstance(PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Reset the singleton — used between test cases.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function buildDsnFromEnv(): string
    {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $name = $_ENV['DB_NAME'] ?? 'adwt';

        return "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    }
}

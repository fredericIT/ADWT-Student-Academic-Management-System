<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO connection singleton for SAMS.
 *
 * Reads configuration from config/database.php (or environment variables).
 * If MySQL is unreachable, it automatically falls back to an SQLite database
 * in database/database.sqlite and auto-initializes the schema and seed data.
 */
class Connection
{
    private static ?PDO $instance = null;

    /** Prevent direct instantiation. */
    private function __construct() {}

    /**
     * Return the shared PDO instance, creating and initializing it on first call.
     *
     * @param string|null $dsn      Full PDO DSN (overrides config when provided).
     * @param string|null $user     DB username (overrides config when provided).
     * @param string|null $password DB password (overrides config when provided).
     */
    public static function getInstance(
        ?string $dsn      = null,
        ?string $user     = null,
        ?string $password = null
    ): PDO {
        if (self::$instance === null) {
            $configPath = __DIR__ . '/../../config/database.php';
            $config = file_exists($configPath) ? require $configPath : [];

            if ($dsn !== null) {
                // Explicit DSN provided (e.g., custom setup)
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                self::$instance = new PDO($dsn, $user ?? '', $password ?? '', $options);
            } else {
                // Attempt primary MySQL connection
                $mySqlCfg = $config['connections']['mysql'] ?? [
                    'host'     => $_ENV['DB_HOST'] ?? '127.0.0.1',
                    'port'     => (int) ($_ENV['DB_PORT'] ?? 3306),
                    'database' => $_ENV['DB_NAME'] ?? 'adwt',
                    'username' => $_ENV['DB_USER'] ?? 'root',
                    'password' => $_ENV['DB_PASS'] ?? '',
                ];

                $mySqlDsn = "mysql:host={$mySqlCfg['host']};port={$mySqlCfg['port']};dbname={$mySqlCfg['database']};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                try {
                    self::$instance = new PDO($mySqlDsn, (string) $mySqlCfg['username'], (string) $mySqlCfg['password'], $options);
                } catch (PDOException $e) {
                    // Fallback to SQLite so the application is completely runnable out of the box
                    $sqliteDir = __DIR__ . '/../../database';
                    if (!is_dir($sqliteDir)) {
                        mkdir($sqliteDir, 0777, true);
                    }
                    $sqlitePath = $sqliteDir . '/database.sqlite';
                    $sqliteDsn  = "sqlite:{$sqlitePath}";

                    try {
                        self::$instance = new PDO($sqliteDsn, null, null, [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]);
                        self::$instance->exec('PRAGMA foreign_keys = ON;');
                    } catch (PDOException $sqliteEx) {
                        throw new RuntimeException(
                            'Database connection failed for both MySQL and SQLite: ' . $sqliteEx->getMessage(),
                            (int) $sqliteEx->getCode(),
                            $sqliteEx
                        );
                    }
                }
            }

            self::ensureSchemaAndSeed(self::$instance);
        }

        return self::$instance;
    }

    /**
     * Replace the shared instance — used by tests to inject an in-memory SQLite PDO.
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

    /**
     * Ensure all required tables and initial seed data exist in the database.
     */
    public static function ensureSchemaAndSeed(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Check if departments table exists
        $hasTables = false;
        try {
            if ($driver === 'sqlite') {
                $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='departments'");
                $hasTables = (bool) $check->fetchColumn();
            } else {
                $check = $pdo->query("SHOW TABLES LIKE 'departments'");
                $hasTables = (bool) $check->fetchColumn();
            }
        } catch (\Throwable) {
            $hasTables = false;
        }

        if (!$hasTables) {
            self::runSqlScript($pdo, __DIR__ . '/../../database/schema.sql');
            self::runSqlScript($pdo, __DIR__ . '/../../database/seed.sql');
        } else {
            // Also ensure users table exists if schema was from older migration
            $hasUsers = false;
            try {
                if ($driver === 'sqlite') {
                    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
                    $hasUsers = (bool) $check->fetchColumn();
                } else {
                    $check = $pdo->query("SHOW TABLES LIKE 'users'");
                    $hasUsers = (bool) $check->fetchColumn();
                }
            } catch (\Throwable) {
                $hasUsers = false;
            }

            if (!$hasUsers) {
                self::runSqlScript($pdo, __DIR__ . '/../../database/schema.sql');
                self::runSqlScript($pdo, __DIR__ . '/../../database/seed.sql');
            }
        }
    }

    private static function runSqlScript(PDO $pdo, string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false || trim($sql) === '') {
            return;
        }

        // Strip comments first so statements are clean
        $sql = preg_replace('/--.*?$/m', '', $sql);

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sql = preg_replace('/ENGINE=InnoDB.*?;/is', ';', $sql);
            $sql = preg_replace('/\bINT\s+UNSIGNED\s+AUTO_INCREMENT\s+PRIMARY\s+KEY\b/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
            $sql = preg_replace('/\bTINYINT\s+UNSIGNED\b/i', 'INTEGER', $sql);
            $sql = preg_replace('/\bINT\s+UNSIGNED\b/i', 'INTEGER', $sql);
            $sql = preg_replace('/\bDECIMAL\(\d+,\d+\)\b/i', 'REAL', $sql);
            $sql = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $sql);
            $sql = preg_replace('/ON\s+DUPLICATE\s+KEY\s+UPDATE.*?(;|$)/is', ';', $sql);
            $sql = preg_replace('/\bINSERT\s+INTO\b/i', 'INSERT OR REPLACE INTO', $sql);
        }

        // Split queries by semicolon
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn(string $stmt) => $stmt !== ''
        );

        foreach ($statements as $stmt) {
            try {
                $pdo->exec($stmt);
            } catch (\Throwable) {
                // Ignore idempotent re-creation warnings
            }
        }
    }
}

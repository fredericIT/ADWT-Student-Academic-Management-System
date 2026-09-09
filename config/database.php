<?php

declare(strict_types=1);

/**
 * Centralized Database Configuration for SAMS.
 *
 * Reads database parameters from environment variables or .env,
 * falling back to local MySQL defaults (or SQLite fallback if MySQL is unreachable).
 */
return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',

    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port'      => (int) ($_ENV['DB_PORT'] ?? 3306),
            'database'  => $_ENV['DB_NAME'] ?? 'adwt',
            'username'  => $_ENV['DB_USER'] ?? 'root',
            'password'  => $_ENV['DB_PASS'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options'   => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ],
        ],

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../database/database.sqlite',
            'options'  => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
    ],
];

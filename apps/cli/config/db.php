<?php declare(strict_types=1);

return [
    'db' => [
        'prefix' => '',
        'driver' => 'mysql',
        'host' => 'db',
        'port' => 3306,
        'database' => 'concept_api',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'log_enabled' => false,
        'log_file' => 'query.log',
        'log_max_files' => 7,
    ],
    'migrations' => [
        'table' => 'migrations',
        // Shared schema with apps/api (ops entry for the same database).
        'paths' => [
            '../api/database/migrations',
        ],
    ],
];

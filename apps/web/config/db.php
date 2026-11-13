<?php declare(strict_types=1);

use Concept\App\Database\Seeders\UserSeeder;

return [
    'db' => [
        'prefix' => '',
        'driver' => 'mysql',
        'host' => 'db',
        'port' => 3306,
        'database' => 'concept_web',
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
        'paths' => [
            'database/migrations',
        ],
    ],
    'seeders' => [
        'list' => [
            UserSeeder::class,
        ],
    ],
];

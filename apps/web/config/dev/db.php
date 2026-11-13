<?php declare(strict_types=1);

use Concept\App\Database\Seeders\BlogContentSeeder;
use Concept\App\Database\Seeders\UserSeeder;

return [
    'seeders' => [
        'list' => [
            UserSeeder::class,
            BlogContentSeeder::class,
        ],
    ],
];

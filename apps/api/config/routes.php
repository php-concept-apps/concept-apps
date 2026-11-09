<?php declare(strict_types=1);

use Concept\Components\AuthApiToken\Routing\UserArgumentResolver;

return [
    'routes' => [
        'list' => [
            'routes/api.php',
        ],
        // Appended after built-in Http chain (FormRequest, ServerRequest, TypedRoute, RouteParameter).
        'resolvers' => [
            UserArgumentResolver::class,
        ],
    ],
];

<?php declare(strict_types=1);

return [
    'masking' => [
        'patterns' => [
            '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.[a-z]{2,}/i' => '***@***.***',
            '/\d{4}-\d{4}-\d{4}-\d{4}/' => '****-****-****-****',
            '/(password|passwd|pwd|token|api_key|secret|authorization)[:=]+([^\s,;]+)/i' => '$1=*****',
        ],
        'key_patterns' => [
            '/.*password.*/i',
            '/.*token.*/i',
            '/.*secret.*/i',
            '/api_key/i',
            '/authorization/i',
        ],
        'rules' => [],
    ],
];

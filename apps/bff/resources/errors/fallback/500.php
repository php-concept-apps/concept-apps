<?php declare(strict_types=1);

use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Concept\Extensions\Http\Protocol\HttpValue;

header('Content-Type: ' . HttpValue::JSON);

$code = HttpStatusCode::INTERNAL_SERVER_ERROR;

echo json_encode([
    'status' => 'error',
    'code' => $code,
    'message' => 'Internal Server Error',
], JSON_THROW_ON_ERROR);

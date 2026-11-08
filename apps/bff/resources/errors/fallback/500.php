<?php declare(strict_types=1);

use Concept\App\Http\Response\JsonEnvelope;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Concept\Extensions\Http\Protocol\HttpValue;

header('Content-Type: ' . HttpValue::JSON);

$code = HttpStatusCode::INTERNAL_SERVER_ERROR;

echo json_encode(
    JsonEnvelope::error('Internal Server Error', $code),
    JSON_THROW_ON_ERROR,
);

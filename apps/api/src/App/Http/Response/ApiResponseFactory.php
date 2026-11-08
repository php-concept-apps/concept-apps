<?php declare(strict_types=1);

namespace Concept\App\Http\Response;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;

final class ApiResponseFactory implements ApiResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responses,
    ) {}

    public function success(
        mixed $payload = [],
        int $code = HttpStatusCode::OK,
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface {
        return $this->responses->json(JsonEnvelope::success($payload), $code, $jsonFlags);
    }

    public function error(
        string $message,
        int $code = HttpStatusCode::INTERNAL_SERVER_ERROR,
        array $details = [],
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface {
        return $this->responses->json(JsonEnvelope::error($message, $code, $details), $code, $jsonFlags);
    }
}

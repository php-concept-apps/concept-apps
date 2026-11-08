<?php declare(strict_types=1);

namespace Concept\App\Http\Contracts;

use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Http\Message\ResponseInterface;

interface ApiResponseFactoryInterface
{
    public function success(
        mixed $payload = [],
        int $code = HttpStatusCode::OK,
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface;

    /**
     * @param array<string, mixed> $details
     */
    public function error(
        string $message,
        int $code = HttpStatusCode::INTERNAL_SERVER_ERROR,
        array $details = [],
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface;

    /**
     * @param LengthAwarePaginator<int, mixed> $paginator
     */
    public function paginated(
        LengthAwarePaginator $paginator,
        int $code = HttpStatusCode::OK,
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface;
}

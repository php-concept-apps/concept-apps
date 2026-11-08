<?php declare(strict_types=1);

namespace Concept\App\Http\Response;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
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

    public function paginated(
        LengthAwarePaginator $paginator,
        int $code = HttpStatusCode::OK,
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ): ResponseInterface {
        $items = [];
        foreach ($paginator->items() as $item) {
            if ($item instanceof Arrayable) {
                $items[] = $item->toArray();
                continue;
            }

            $items[] = $item;
        }

        return $this->success([
            JsonEnvelope::ITEMS => $items,
            JsonEnvelope::META => [
                JsonEnvelope::META_CURRENT_PAGE => $paginator->currentPage(),
                JsonEnvelope::META_PER_PAGE => $paginator->perPage(),
                JsonEnvelope::META_TOTAL => $paginator->total(),
                JsonEnvelope::META_LAST_PAGE => $paginator->lastPage(),
                JsonEnvelope::META_FROM => $paginator->firstItem(),
                JsonEnvelope::META_TO => $paginator->lastItem(),
            ],
            JsonEnvelope::LINKS => [
                JsonEnvelope::LINK_FIRST => $paginator->url(1),
                JsonEnvelope::LINK_LAST => $paginator->url(max(1, $paginator->lastPage())),
                JsonEnvelope::LINK_PREV => $paginator->previousPageUrl(),
                JsonEnvelope::LINK_NEXT => $paginator->nextPageUrl(),
            ],
        ], $code, $jsonFlags);
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Middleware;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Concept\Extensions\ValidationRakit\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HandleJsonValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            return $this->apiResponseFactory->error(
                $e->getMessage(),
                HttpStatusCode::UNPROCESSABLE_ENTITY,
                $e->getErrors(),
            );
        }
    }
}

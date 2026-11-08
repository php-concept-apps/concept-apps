<?php declare(strict_types=1);

namespace Concept\App\Http\Error;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\Extensions\ErrorHandlerWhoops\Contracts\HttpErrorRendererInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ApiHttpErrorRenderer implements HttpErrorRendererInterface
{
    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $errors
     */
    public function render(
        ServerRequestInterface $request,
        int $code,
        ?string $message = null,
        array $data = [],
        array $errors = [],
    ): ResponseInterface {
        return $this->apiResponseFactory->error(
            $message ?? HttpStatusCode::getReasonPhrase($code),
            $code,
            $errors,
        );
    }
}

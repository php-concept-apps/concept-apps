<?php declare(strict_types=1);

namespace Concept\App\Middleware;

use Concept\App\Services\AuthService;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** Redirect authenticated users away from guest-only pages (login). */
final class GuestMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->auth->check()) {
            return $this->responseFactory->redirectByName('admin.home');
        }

        return $handler->handle($request);
    }
}

<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * No Authorization → continue anonymous.
 * Authorization present but invalid → UnauthorizedException (strict).
 * Valid Bearer → auth attributes on request.
 */
final class OptionalAuthenticateBearerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly BearerRequestAuthenticator $authenticator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $plain = $this->authenticator->extractPlainToken($request);
        if ($plain === null) {
            return $handler->handle($request);
        }

        $auth = $this->authenticator->authenticatePlainToken($plain);
        $request = $this->authenticator->withAuthAttributes($request, $auth);

        return $handler->handle($request);
    }
}

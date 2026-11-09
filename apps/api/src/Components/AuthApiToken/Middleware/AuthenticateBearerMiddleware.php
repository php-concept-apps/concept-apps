<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Middleware;

use Concept\Components\AuthApiToken\Exceptions\UnauthorizedException;
use Concept\Components\AuthApiToken\Models\User;
use Concept\Components\AuthApiToken\Services\TokenAuthService;
use Concept\Components\AuthApiToken\Support\AuthAttribute;
use Concept\Extensions\Http\Protocol\HttpHeader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticateBearerMiddleware implements MiddlewareInterface
{
    private const string BEARER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly TokenAuthService $tokenAuth,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $plain = $this->extractBearerToken($request);
        if ($plain === null) {
            throw new UnauthorizedException();
        }

        $token = $this->tokenAuth->resolve($plain);
        if ($token === null) {
            throw new UnauthorizedException();
        }

        $user = $token->getRelation('user');
        if (!($user instanceof User)) {
            throw new UnauthorizedException();
        }

        $request = $request
            ->withAttribute(AuthAttribute::USER, $user)
            ->withAttribute(AuthAttribute::TOKEN, $token);

        return $handler->handle($request);
    }

    private function extractBearerToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine(HttpHeader::AUTHORIZATION);
        if ($header === '' || !str_starts_with($header, self::BEARER_PREFIX)) {
            return null;
        }

        $token = trim(substr($header, strlen(self::BEARER_PREFIX)));

        return $token !== '' ? $token : null;
    }
}

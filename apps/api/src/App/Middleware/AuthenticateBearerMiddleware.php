<?php declare(strict_types=1);

namespace Concept\App\Middleware;

use Concept\App\Auth\AuthAttribute;
use Concept\App\Auth\TokenAuthService;
use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Models\User;
use Concept\Extensions\Http\Protocol\HttpHeader;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticateBearerMiddleware implements MiddlewareInterface
{
    private const string ERR_UNAUTHORIZED = 'Unauthorized';
    private const string BEARER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly TokenAuthService $tokenAuth,
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $plain = $this->extractBearerToken($request);
        if ($plain === null) {
            return $this->unauthorized();
        }

        $token = $this->tokenAuth->resolve($plain);
        if ($token === null) {
            return $this->unauthorized();
        }

        $user = $token->getRelation('user');
        if (!($user instanceof User)) {
            return $this->unauthorized();
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

    private function unauthorized(): ResponseInterface
    {
        return $this->apiResponseFactory->error(self::ERR_UNAUTHORIZED, HttpStatusCode::UNAUTHORIZED);
    }
}

<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Middleware;

use Concept\Components\AuthApiToken\Exceptions\UnauthorizedException;
use Concept\Components\AuthApiToken\Models\User;
use Concept\Components\AuthApiToken\Services\TokenAuthService;
use Concept\Components\AuthApiToken\Support\AuthAttribute;
use Concept\Components\AuthApiToken\Support\AuthenticatedBearer;
use Concept\Extensions\Http\Protocol\HttpHeader;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Shared Bearer resolve for require / optional middlewares.
 * Missing Authorization → null. Present but invalid → UnauthorizedException.
 */
final class BearerRequestAuthenticator
{
    private const string BEARER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly TokenAuthService $tokenAuth,
    ) {}

    public function extractPlainToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine(HttpHeader::AUTHORIZATION);
        if ($header === '' || !str_starts_with($header, self::BEARER_PREFIX)) {
            return null;
        }

        $token = trim(substr($header, strlen(self::BEARER_PREFIX)));

        return $token !== '' ? $token : null;
    }

    public function authenticatePlainToken(string $plain): AuthenticatedBearer
    {
        $token = $this->tokenAuth->resolve($plain);
        if ($token === null) {
            throw new UnauthorizedException();
        }

        $user = $token->getRelation('user');
        if (!($user instanceof User)) {
            throw new UnauthorizedException();
        }

        return new AuthenticatedBearer($user, $token);
    }

    public function withAuthAttributes(
        ServerRequestInterface $request,
        AuthenticatedBearer $auth,
    ): ServerRequestInterface {
        return $request
            ->withAttribute(AuthAttribute::USER, $auth->user)
            ->withAttribute(AuthAttribute::TOKEN, $auth->token);
    }
}

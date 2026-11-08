<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Auth\AuthAttribute;
use Concept\App\Auth\TokenAuthService;
use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Http\Requests\LoginRequest;
use Concept\App\Models\ApiToken;
use Concept\App\Models\User;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController
{
    private const string ERR_INVALID_CREDENTIALS = 'Invalid credentials';

    public function __construct(
        private readonly TokenAuthService $tokenAuth,
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    public function login(LoginRequest $request): ResponseInterface
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        if (!is_string($email) || !is_string($password)) {
            return $this->apiResponseFactory->error(
                self::ERR_INVALID_CREDENTIALS,
                HttpStatusCode::UNAUTHORIZED,
            );
        }

        $tokenName = $data['token_name'] ?? null;
        if (!is_string($tokenName) || $tokenName === '') {
            $tokenName = null;
        }

        $issued = $this->tokenAuth->attempt($email, $password, $tokenName);
        if ($issued === null) {
            return $this->apiResponseFactory->error(
                self::ERR_INVALID_CREDENTIALS,
                HttpStatusCode::UNAUTHORIZED,
            );
        }

        return $this->apiResponseFactory->success([
            'token' => $issued->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $issued->user->toArray(),
        ]);
    }

    public function me(User $user): ResponseInterface
    {
        return $this->apiResponseFactory->success($user->toArray());
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $token = $request->getAttribute(AuthAttribute::TOKEN);
        if ($token instanceof ApiToken) {
            $this->tokenAuth->revoke($token);
        }

        return $this->apiResponseFactory->success([
            'revoked' => true,
        ]);
    }
}

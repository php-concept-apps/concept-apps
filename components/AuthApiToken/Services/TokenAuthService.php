<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Services;

use Concept\Components\AuthApiToken\Models\ApiToken;
use Concept\Components\AuthApiToken\Models\User;
use Concept\Components\AuthApiToken\Support\IssuedToken;
use DateTimeImmutable;

final class TokenAuthService
{
    private const int TOKEN_BYTES = 32;

    public function attempt(string $email, string $password, ?string $tokenName = null): ?IssuedToken
    {
        $user = User::query()->where('email', $email)->first();
        if (!($user instanceof User) || !$user->verifyPassword($password)) {
            return null;
        }

        return $this->issue($user, $tokenName);
    }

    public function issue(User $user, ?string $tokenName = null): IssuedToken
    {
        $plain = bin2hex(random_bytes(self::TOKEN_BYTES));

        $token = new ApiToken([
            'user_id' => $user->getKey(),
            'name' => $tokenName,
            'token_hash' => $this->hash($plain),
        ]);
        $token->save();

        return new IssuedToken($user, $token, $plain);
    }

    public function resolve(string $plainTextToken): ?ApiToken
    {
        if ($plainTextToken === '') {
            return null;
        }

        $token = ApiToken::query()
            ->with('user')
            ->where('token_hash', $this->hash($plainTextToken))
            ->first();

        if (!($token instanceof ApiToken) || $token->isExpired()) {
            return null;
        }

        $user = $token->getRelation('user');
        if (!($user instanceof User)) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => new DateTimeImmutable(),
        ])->save();

        return $token;
    }

    public function revoke(ApiToken $token): void
    {
        $token->delete();
    }

    private function hash(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }
}

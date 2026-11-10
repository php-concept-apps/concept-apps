<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Support;

use Concept\Components\AuthApiToken\Models\ApiToken;
use Concept\Components\AuthApiToken\Models\User;

/**
 * Plaintext token is returned only at issue time; only the hash is stored.
 */
final readonly class IssuedToken
{
    public function __construct(
        public User $user,
        public ApiToken $token,
        public string $plainTextToken,
    ) {}
}

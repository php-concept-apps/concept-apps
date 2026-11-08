<?php declare(strict_types=1);

namespace Concept\App\Auth;

use Concept\App\Models\ApiToken;
use Concept\App\Models\User;

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

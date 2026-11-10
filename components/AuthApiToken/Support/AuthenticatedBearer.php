<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Support;

use Concept\Components\AuthApiToken\Models\ApiToken;
use Concept\Components\AuthApiToken\Models\User;

final readonly class AuthenticatedBearer
{
    public function __construct(
        public User $user,
        public ApiToken $token,
    ) {}
}

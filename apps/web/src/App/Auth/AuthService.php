<?php declare(strict_types=1);

namespace Concept\App\Auth;

use Concept\App\Models\User;
use Concept\Extensions\SessionSymfony\Contracts\SessionInterface;

final class AuthService
{
    private const string SESSION_KEY = 'auth_user_id';

    private ?User $user = null;

    public function __construct(
        private readonly SessionInterface $session,
    ) {}

    public function attempt(string $email, string $password, bool $remember = false): ?User
    {
        /** @var User|null $user */
        $user = User::query()->where(User::FIELD_EMAIL, $email)->first();

        if ($user === null || !$user->verifyPassword($password) || !$user->isActive()) {
            return null;
        }

        $this->login($user, $remember);

        return $user;
    }

    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $id = $this->session->get(self::SESSION_KEY);
        if (!is_numeric($id)) {
            return null;
        }

        /** @var User|null $user */
        $user = User::query()->whereKey((int) $id)->first();
        if ($user === null || !$user->isActive()) {
            $this->logout();

            return null;
        }

        return $this->user = $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function login(User $user, bool $remember = false): void
    {
        $this->session->migrate(true);
        $this->session->set(self::SESSION_KEY, $user->getId());
        $this->user = $user;

        if ($remember) {
            $user->setRememberToken(bin2hex(random_bytes(32)));
            $user->save();
        }
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_KEY);
        $this->session->migrate(true);
        $this->user = null;
    }
}

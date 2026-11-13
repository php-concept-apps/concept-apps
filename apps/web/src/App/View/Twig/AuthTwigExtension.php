<?php declare(strict_types=1);

namespace Concept\App\View\Twig;

use Concept\App\Auth\AuthService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AuthTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('auth', fn(): AuthService => $this->auth),
        ];
    }
}

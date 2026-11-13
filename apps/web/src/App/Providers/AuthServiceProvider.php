<?php declare(strict_types=1);

namespace Concept\App\Providers;

use Concept\App\Auth\AuthService;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\SessionSymfony\Contracts\SessionInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class AuthServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return $id === AuthService::class;
    }

    public function register(): void
    {
        $container = $this->getContainer();

        $container->add(
            AuthService::class,
            static fn(): AuthService => new AuthService(
                ContainerDependency::get($container, SessionInterface::class),
            ),
        )->setShared(true);
    }
}

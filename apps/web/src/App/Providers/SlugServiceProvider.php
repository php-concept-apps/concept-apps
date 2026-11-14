<?php declare(strict_types=1);

namespace Concept\App\Providers;

use Concept\App\Slug\SlugService;
use Concept\Core\Container\ContainerDependency;
use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class SlugServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return $id === SlugifyInterface::class || $id === SlugService::class;
    }

    public function register(): void
    {
        $container = $this->getContainer();

        $container->add(
            SlugifyInterface::class,
            static fn(): SlugifyInterface => new Slugify(),
        )->setShared(true);

        $container->add(
            SlugService::class,
            static fn(): SlugService => new SlugService(
                ContainerDependency::get($container, SlugifyInterface::class),
            ),
        )->setShared(true);
    }
}

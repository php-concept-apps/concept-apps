<?php declare(strict_types=1);

namespace Concept\App\Providers;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Http\Response\ApiResponseFactory;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class ApiResponseServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return $id === ApiResponseFactoryInterface::class;
    }

    public function register(): void
    {
        $container = $this->getContainer();

        $container->add(
            ApiResponseFactoryInterface::class,
            static fn(): ApiResponseFactory => new ApiResponseFactory(
                ContainerDependency::get($container, ResponseFactoryInterface::class),
            ),
        )->setShared(true);
    }
}

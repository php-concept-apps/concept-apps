<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Providers\AuthServiceProvider;
use Concept\Core\App;
use Concept\Extensions\DatabaseEloquent\PaginationConfiguratorServiceProvider;

/** App-owned service providers (not ConceptStack capabilities). */
final class ApplicationServiceProvidersBootstrap
{
    public function boot(App $app): void
    {
        $app->registerServiceProviders([
            new AuthServiceProvider(),
            new PaginationConfiguratorServiceProvider(),
        ]);
    }
}

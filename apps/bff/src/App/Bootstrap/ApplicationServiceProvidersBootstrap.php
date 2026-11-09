<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Providers\ApiResponseServiceProvider;
use Concept\Core\App;

/**
 * App-owned service providers (not ConceptStack capabilities).
 */
final class ApplicationServiceProvidersBootstrap
{
    public function boot(App $app): void
    {
        $app->registerServiceProviders([
            new ApiResponseServiceProvider(),
        ]);
    }
}

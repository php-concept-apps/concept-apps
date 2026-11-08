<?php declare(strict_types=1);

use Concept\App\Controllers\EchoController;
use Concept\App\Controllers\HealthController;
use Concept\App\Middleware\HandleJsonValidationExceptionMiddleware;
use Concept\Extensions\Json\Middleware\ForceJsonResponseMiddleware;
use Concept\Extensions\Json\Middleware\ParseJsonBodyMiddleware;
use League\Route\RouteGroup;
use League\Route\Router;

/** @var Router $router */
$router->group('/api', function(RouteGroup $router): void {
    $router->get('/health', HealthController::class)->setName('api.health');
    $router->post('/echo', EchoController::class)->setName('api.echo');
})->lazyMiddlewares([
    ParseJsonBodyMiddleware::class,
    ForceJsonResponseMiddleware::class,
    HandleJsonValidationExceptionMiddleware::class,
]);

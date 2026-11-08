<?php declare(strict_types=1);

use Concept\App\Controllers\DbController;
use Concept\App\Controllers\EchoController;
use Concept\App\Controllers\HealthController;
use Concept\App\Controllers\ProductController;
use Concept\App\Middleware\HandleJsonValidationExceptionMiddleware;
use Concept\Extensions\Json\Middleware\ForceJsonResponseMiddleware;
use Concept\Extensions\Json\Middleware\ParseJsonBodyMiddleware;
use League\Route\RouteGroup;
use League\Route\Router;

/** @var Router $router */
$router->group('/api', function(RouteGroup $router): void {
    $router->get('/health', [HealthController::class, 'check'])->setName('api.health');
    $router->get('/db', [DbController::class, 'status'])->setName('api.db');
    $router->get('/products', [ProductController::class, 'index'])->setName('api.products.index');
    $router->post('/products', [ProductController::class, 'store'])->setName('api.products.store');
    $router->get('/products/{id}', [ProductController::class, 'show'])->setName('api.products.show');
    $router->patch('/products/{id}', [ProductController::class, 'update'])->setName('api.products.update');
    $router->delete('/products/{id}', [ProductController::class, 'destroy'])->setName('api.products.destroy');
    $router->post('/echo', [EchoController::class, 'echo'])->setName('api.echo');
})->lazyMiddlewares([
    ParseJsonBodyMiddleware::class,
    ForceJsonResponseMiddleware::class,
    HandleJsonValidationExceptionMiddleware::class,
]);

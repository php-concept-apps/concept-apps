<?php declare(strict_types=1);

use Concept\App\Controllers\IndexController;
use Concept\App\Middleware\HandleCsrfExceptionMiddleware;
use Concept\App\Middleware\HandleValidationExceptionMiddleware;
use Concept\App\Middleware\ShareViewDataMiddleware;
use Concept\App\Middleware\StorePreviousUrlMiddleware;
use Concept\Extensions\Csrf\Middleware\VerifyCsrfTokenMiddleware;
use League\Route\RouteGroup;
use League\Route\Router;

/** @var Router $router */
$router->lazyMiddlewares([
    StorePreviousUrlMiddleware::class,
    HandleValidationExceptionMiddleware::class,
    HandleCsrfExceptionMiddleware::class,
    VerifyCsrfTokenMiddleware::class,
    ShareViewDataMiddleware::class,
]);

$router->group('', function(RouteGroup $router): void {
    $router->get('/', [IndexController::class, 'index'])->setName('home');
    $router->post('/echo', [IndexController::class, 'echo'])->setName('echo');
    $router->get('/hello/{name}', [IndexController::class, 'hello'])->setName('hello');
});

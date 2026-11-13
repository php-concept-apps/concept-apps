<?php declare(strict_types=1);

use Concept\App\Controllers\Admin\AuthController;
use Concept\App\Controllers\Admin\DashboardController;
use Concept\App\Middleware\AuthMiddleware;
use Concept\App\Middleware\GuestMiddleware;
use Concept\App\Middleware\HandleCsrfExceptionMiddleware;
use Concept\App\Middleware\HandleValidationExceptionMiddleware;
use Concept\App\Middleware\ShareViewDataMiddleware;
use Concept\App\Middleware\StorePreviousUrlMiddleware;
use Concept\Extensions\Csrf\Middleware\VerifyCsrfTokenMiddleware;
use League\Route\RouteGroup;
use League\Route\Router;

/** @var Router $router */
$webStack = [
    StorePreviousUrlMiddleware::class,
    HandleValidationExceptionMiddleware::class,
    HandleCsrfExceptionMiddleware::class,
    VerifyCsrfTokenMiddleware::class,
    ShareViewDataMiddleware::class,
];

$router->group('/admin', function(RouteGroup $router): void {
    $router->get('/login', [AuthController::class, 'showLogin'])->setName('admin.login');
    $router->post('/login', [AuthController::class, 'login'])->setName('admin.login.submit');
})->lazyMiddlewares([
    ...$webStack,
    GuestMiddleware::class,
]);

$router->group('/admin', function(RouteGroup $router): void {
    $router->get('/', [DashboardController::class, 'index'])->setName('admin.home');
    $router->post('/logout', [AuthController::class, 'logout'])->setName('admin.logout');
})->lazyMiddlewares([
    ...$webStack,
    AuthMiddleware::class,
]);

<?php declare(strict_types=1);

use Concept\App\Controllers\BlogController;
use Concept\App\Controllers\HomeController;
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
    $router->get('/', [HomeController::class, 'index'])->setName('home');

    $router->get('/blog', [BlogController::class, 'index'])->setName('blog');
    $router->get('/blog/category/{slug}', [BlogController::class, 'category'])->setName('blog.category');
    $router->get('/blog/tag/{slug}', [BlogController::class, 'tag'])->setName('blog.tag');
    $router->get('/blog/{slug}', [BlogController::class, 'show'])->setName('blog.show');
});

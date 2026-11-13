<?php declare(strict_types=1);

use Concept\App\Controllers\Admin\AuthController;
use Concept\App\Controllers\Admin\CategoryController;
use Concept\App\Controllers\Admin\DashboardController;
use Concept\App\Controllers\Admin\PostController;
use Concept\App\Controllers\Admin\TagController;
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

    $router->get('/posts', [PostController::class, 'index'])->setName('admin.posts');
    $router->get('/posts/create', [PostController::class, 'create'])->setName('admin.posts.create');
    $router->post('/posts/store', [PostController::class, 'store'])->setName('admin.posts.store');
    $router->get('/posts/edit/{id:number}', [PostController::class, 'edit'])->setName('admin.posts.edit');
    $router->post('/posts/update/{id:number}', [PostController::class, 'update'])->setName('admin.posts.update');
    $router->post('/posts/delete/{id:number}', [PostController::class, 'destroy'])->setName('admin.posts.destroy');

    $router->get('/categories', [CategoryController::class, 'index'])->setName('admin.categories');
    $router->get('/categories/create', [CategoryController::class, 'create'])->setName('admin.categories.create');
    $router->post('/categories/store', [CategoryController::class, 'store'])->setName('admin.categories.store');
    $router->get('/categories/edit/{id:number}', [CategoryController::class, 'edit'])->setName('admin.categories.edit');
    $router->post('/categories/update/{id:number}', [CategoryController::class, 'update'])->setName('admin.categories.update');
    $router->post('/categories/delete/{id:number}', [CategoryController::class, 'destroy'])->setName('admin.categories.destroy');

    $router->get('/tags', [TagController::class, 'index'])->setName('admin.tags');
    $router->get('/tags/create', [TagController::class, 'create'])->setName('admin.tags.create');
    $router->post('/tags/store', [TagController::class, 'store'])->setName('admin.tags.store');
    $router->get('/tags/edit/{id:number}', [TagController::class, 'edit'])->setName('admin.tags.edit');
    $router->post('/tags/update/{id:number}', [TagController::class, 'update'])->setName('admin.tags.update');
    $router->post('/tags/delete/{id:number}', [TagController::class, 'destroy'])->setName('admin.tags.destroy');
})->lazyMiddlewares([
    ...$webStack,
    AuthMiddleware::class,
]);

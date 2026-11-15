<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Services\BlogService;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class HomeController
{
    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly BlogService $blog,
    ) {}

    public function index(): ResponseInterface
    {
        return $this->view->create('@frontend/home', [
            'app_nav' => 'home',
            'recent_posts' => $this->blog->recentPublished(3),
        ]);
    }
}

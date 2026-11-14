<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Services\CategoryService;
use Concept\App\Services\PostService;
use Concept\App\Services\TagService;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class DashboardController
{
    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly PostService $posts,
        private readonly CategoryService $categories,
        private readonly TagService $tags,
    ) {}

    public function index(): ResponseInterface
    {
        return $this->view->create('@dashboard/home', [
            'admin_nav' => 'dashboard',
            'posts_count' => $this->posts->count(),
            'categories_count' => $this->categories->count(),
            'tags_count' => $this->tags->count(),
        ]);
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Models\Category;
use Concept\App\Models\Post;
use Concept\App\Models\Tag;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class DashboardController
{
    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
    ) {}

    public function index(): ResponseInterface
    {
        return $this->view->create('@dashboard/home', [
            'admin_nav' => 'dashboard',
            'posts_count' => Post::query()->count(),
            'categories_count' => Category::query()->count(),
            'tags_count' => Tag::query()->count(),
        ]);
    }
}

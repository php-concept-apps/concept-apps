<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Listing\ListFilterSupport;
use Concept\App\Models\Category;
use Concept\App\Models\Post;
use Concept\App\Models\Tag;
use Concept\App\Services\BlogService;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BlogController
{

    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly BlogService $blog,
        private readonly ConfigInterface $config,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $search = ListFilterSupport::optionalString($request->getQueryParams()['q'] ?? null);

        return $this->view->create('@frontend/blog/index', $this->listPayload(
            $this->blog->paginatePublished($this->perPage(), $search),
            'Blog',
            $search,
        ));
    }

    public function category(ServerRequestInterface $request, string $slug): ResponseInterface
    {
        try {
            $category = $this->blog->findCategoryBySlugOrFail($slug);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        $search = ListFilterSupport::optionalString($request->getQueryParams()['q'] ?? null);

        return $this->view->create('@frontend/blog/index', $this->listPayload(
            $this->blog->paginateByCategorySlug($slug, $this->perPage(), $search),
            $category->getName(),
            $search,
            $category,
        ));
    }

    public function tag(ServerRequestInterface $request, string $slug): ResponseInterface
    {
        try {
            $tag = $this->blog->findTagBySlugOrFail($slug);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        $search = ListFilterSupport::optionalString($request->getQueryParams()['q'] ?? null);

        return $this->view->create('@frontend/blog/index', $this->listPayload(
            $this->blog->paginateByTagSlug($slug, $this->perPage(), $search),
            $tag->getName(),
            $search,
            tag: $tag,
        ));
    }

    public function show(string $slug): ResponseInterface
    {
        try {
            $post = $this->blog->findPublishedBySlugOrFail($slug);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->view->create('@frontend/blog/show', [
            'app_nav' => 'blog',
            'post' => $post,
            'recent_posts' => $this->blog->recentPublished(),
            'sidebar_categories' => $this->blog->categoriesWithPublishedCount(),
            'sidebar_tags' => $this->blog->tagsInUse(),
            'search' => null,
        ]);
    }

    /**
     * @param LengthAwarePaginator<int, Post> $posts
     * @return array<string, mixed>
     */
    private function listPayload(
        LengthAwarePaginator $posts,
        string $heading,
        ?string $search,
        ?Category $category = null,
        ?Tag $tag = null,
    ): array {
        return [
            'app_nav' => 'blog',
            'posts' => $posts,
            'page_heading' => $heading,
            'search' => $search,
            'category' => $category,
            'tag' => $tag,
            'recent_posts' => $this->blog->recentPublished(),
            'sidebar_categories' => $this->blog->categoriesWithPublishedCount(),
            'sidebar_tags' => $this->blog->tagsInUse(),
        ];
    }

    private function perPage(): int
    {
        return $this->config->getInt(ConfigKey::PAGINATION_PER_PAGE);
    }

    private function notFound(): ResponseInterface
    {
        return $this->view->create('@frontend/errors/404', [
            'app_nav' => 'blog',
        ], HttpStatusCode::NOT_FOUND);
    }
}

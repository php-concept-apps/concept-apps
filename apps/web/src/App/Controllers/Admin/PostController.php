<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Auth\AuthService;
use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Dto\Post\UpdatePostDto;
use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\DtoException;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Exceptions\RuntimeException;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Http\Listing\PostListFilter;
use Concept\App\Http\Listing\SortQuery;
use Concept\App\Http\Requests\Admin\StorePostRequest;
use Concept\App\Http\Requests\Admin\UpdatePostRequest;
use Concept\App\Models\Tag;
use Concept\App\Services\CategoryService;
use Concept\App\Services\PostService;
use Concept\App\Services\TagService;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PostController
{
    private const string MSG_CREATED = 'Post created.';
    private const string MSG_UPDATED = 'Post updated.';
    private const string MSG_DELETED = 'Post deleted.';
    private const string ERR_DTO = 'Post request did not produce a DTO.';
    private const string ERR_AUTHOR = 'Authenticated author is required.';
    private const int DEFAULT_PER_PAGE = 15;

    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly ResponseFactoryInterface $response,
        private readonly FlashBagInterface $flashBag,
        private readonly PostService $posts,
        private readonly CategoryService $categories,
        private readonly TagService $tags,
        private readonly AuthService $auth,
        private readonly ConfigInterface $config,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $sort = SortQuery::from(
            $query,
            PostService::SORTABLE,
            PostService::SORT_DEFAULT,
            SortQuery::DESC,
        );
        $filter = PostListFilter::from($query);

        return $this->view->create('@dashboard/posts/index', [
            'admin_nav' => 'posts',
            'posts' => $this->posts->paginate(
                $this->config->getInt(ConfigKey::PAGINATION_PER_PAGE, self::DEFAULT_PER_PAGE),
                $sort,
                $filter,
            ),
            'sort_column' => $sort->column,
            'sort_dir' => $sort->direction,
            'filter' => $filter,
            'filter_query' => http_build_query($filter->toQueryParams()),
            'statuses' => PostStatus::cases(),
            'categories' => $this->categories->all(),
            'tags' => $this->tags->all(),
        ]);
    }

    public function create(): ResponseInterface
    {
        return $this->view->create('@dashboard/posts/form', [
            'admin_nav' => 'posts',
            'post' => null,
            'statuses' => PostStatus::cases(),
            'categories' => $this->categories->all(),
            'tags' => $this->tags->all(),
            'selected_tag_ids' => [],
        ]);
    }

    public function store(StorePostRequest $request): ResponseInterface
    {
        $dto = $request->toDto();
        if (!$dto instanceof StorePostDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $author = $this->auth->user();
        if ($author === null) {
            throw new RuntimeException(self::ERR_AUTHOR);
        }

        $this->posts->create($dto, $author);
        $this->flashBag->addSuccess(self::MSG_CREATED);

        return $this->response->redirectByName('admin.posts');
    }

    public function edit(int $id): ResponseInterface
    {
        try {
            $post = $this->posts->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.posts');
        }

        $selected = [];
        foreach ($post->tags()->pluck(Tag::FIELD_ID)->all() as $tagId) {
            if (is_numeric($tagId)) {
                $selected[] = (int) $tagId;
            }
        }

        return $this->view->create('@dashboard/posts/form', [
            'admin_nav' => 'posts',
            'post' => $post,
            'statuses' => PostStatus::cases(),
            'categories' => $this->categories->all(),
            'tags' => $this->tags->all(),
            'selected_tag_ids' => $selected,
        ]);
    }

    public function update(UpdatePostRequest $request, int $id): ResponseInterface
    {
        try {
            $post = $this->posts->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.posts');
        }

        $dto = $request->toDto();
        if (!$dto instanceof UpdatePostDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $this->posts->update($post, $dto);
        $this->flashBag->addSuccess(self::MSG_UPDATED);

        return $this->response->redirectByName('admin.posts');
    }

    public function destroy(int $id): ResponseInterface
    {
        try {
            $post = $this->posts->findOrFail($id);
            $this->posts->delete($post);
            $this->flashBag->addSuccess(self::MSG_DELETED);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());
        }

        return $this->response->redirectByName('admin.posts');
    }
}

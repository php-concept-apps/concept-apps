<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Dto\Tag\StoreTagDto;
use Concept\App\Dto\Tag\UpdateTagDto;
use Concept\App\Exceptions\DtoException;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Listing\SortQuery;
use Concept\App\Listing\TagListFilter;
use Concept\App\Http\Requests\Admin\StoreTagRequest;
use Concept\App\Http\Requests\Admin\UpdateTagRequest;
use Concept\App\Services\TagService;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TagController
{
    private const string MSG_CREATED = 'Tag created.';
    private const string MSG_UPDATED = 'Tag updated.';
    private const string MSG_DELETED = 'Tag deleted.';
    private const string ERR_DTO = 'Tag request did not produce a DTO.';
    private const int DEFAULT_PER_PAGE = 15;

    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly ResponseFactoryInterface $response,
        private readonly FlashBagInterface $flashBag,
        private readonly TagService $tags,
        private readonly ConfigInterface $config,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $sort = SortQuery::from(
            $query,
            TagService::SORTABLE,
            TagService::SORT_DEFAULT,
            SortQuery::DESC,
        );
        $filter = TagListFilter::from($query);

        return $this->view->create('@dashboard/tags/index', [
            'admin_nav' => 'tags',
            'tags' => $this->tags->paginate(
                $this->config->getInt(ConfigKey::PAGINATION_PER_PAGE, self::DEFAULT_PER_PAGE),
                $sort,
                $filter,
            ),
            'sort_column' => $sort->column,
            'sort_dir' => $sort->direction,
            'filter' => $filter,
            'filter_query' => http_build_query($filter->toQueryParams()),
        ]);
    }

    public function create(): ResponseInterface
    {
        return $this->view->create('@dashboard/tags/form', [
            'admin_nav' => 'tags',
            'tag' => null,
        ]);
    }

    public function store(StoreTagRequest $request): ResponseInterface
    {
        $dto = $request->toDto();
        if (!$dto instanceof StoreTagDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $this->tags->create($dto);
        $this->flashBag->addSuccess(self::MSG_CREATED);

        return $this->response->redirectByName('admin.tags');
    }

    public function edit(int $id): ResponseInterface
    {
        try {
            $tag = $this->tags->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.tags');
        }

        return $this->view->create('@dashboard/tags/form', [
            'admin_nav' => 'tags',
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, int $id): ResponseInterface
    {
        try {
            $tag = $this->tags->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.tags');
        }

        $dto = $request->toDto();
        if (!$dto instanceof UpdateTagDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $this->tags->update($tag, $dto);
        $this->flashBag->addSuccess(self::MSG_UPDATED);

        return $this->response->redirectByName('admin.tags');
    }

    public function destroy(int $id): ResponseInterface
    {
        try {
            $tag = $this->tags->findOrFail($id);
            $this->tags->delete($tag);
            $this->flashBag->addSuccess(self::MSG_DELETED);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());
        }

        return $this->response->redirectByName('admin.tags');
    }
}

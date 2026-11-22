<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Dto\Category\StoreCategoryDto;
use Concept\App\Dto\Category\UpdateCategoryDto;
use Concept\App\Exceptions\DtoException;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Listing\CategoryListFilter;
use Concept\App\Listing\SortQuery;
use Concept\App\Http\Requests\Admin\StoreCategoryRequest;
use Concept\App\Http\Requests\Admin\UpdateCategoryRequest;
use Concept\App\Services\CategoryService;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryController
{
    private const string MSG_CREATED = 'Category created.';
    private const string MSG_UPDATED = 'Category updated.';
    private const string MSG_DELETED = 'Category deleted.';
    private const string ERR_DTO = 'Category request did not produce a DTO.';

    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly ResponseFactoryInterface $response,
        private readonly FlashBagInterface $flashBag,
        private readonly CategoryService $categories,
        private readonly ConfigInterface $config,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $sort = SortQuery::from(
            $query,
            CategoryService::SORTABLE,
            CategoryService::SORT_DEFAULT,
            SortQuery::DESC,
        );
        $filter = CategoryListFilter::from($query);

        return $this->view->create('@dashboard/categories/index', [
            'admin_nav' => 'categories',
            'categories' => $this->categories->paginate(
                $this->config->getInt(ConfigKey::PAGINATION_PER_PAGE),
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
        return $this->view->create('@dashboard/categories/form', [
            'admin_nav' => 'categories',
            'category' => null,
        ]);
    }

    public function store(StoreCategoryRequest $request): ResponseInterface
    {
        $dto = $request->toDto();
        if (!$dto instanceof StoreCategoryDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $this->categories->create($dto);
        $this->flashBag->addSuccess(self::MSG_CREATED);

        return $this->response->redirectByName('admin.categories');
    }

    public function edit(int $id): ResponseInterface
    {
        try {
            $category = $this->categories->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.categories');
        }

        return $this->view->create('@dashboard/categories/form', [
            'admin_nav' => 'categories',
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, int $id): ResponseInterface
    {
        try {
            $category = $this->categories->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());

            return $this->response->redirectByName('admin.categories');
        }

        $dto = $request->toDto();
        if (!$dto instanceof UpdateCategoryDto) {
            throw new DtoException(self::ERR_DTO);
        }

        $this->categories->update($category, $dto);
        $this->flashBag->addSuccess(self::MSG_UPDATED);

        return $this->response->redirectByName('admin.categories');
    }

    public function destroy(int $id): ResponseInterface
    {
        try {
            $category = $this->categories->findOrFail($id);
            $this->categories->delete($category);
            $this->flashBag->addSuccess(self::MSG_DELETED);
        } catch (ModelNotFoundException $e) {
            $this->flashBag->addError($e->getMessage());
        }

        return $this->response->redirectByName('admin.categories');
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Category\StoreCategoryDto;
use Concept\App\Dto\Category\UpdateCategoryDto;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Listing\CategoryListFilter;
use Concept\App\Listing\SortQuery;
use Concept\App\Models\Category;
use Concept\App\Services\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CategoryService
{
    private const string ERR_NOT_FOUND = 'Category not found.';

    public const string SORT_DEFAULT = Category::FIELD_ID;

    /** @var list<string> */
    public const array SORTABLE = [
        Category::FIELD_ID,
        Category::FIELD_NAME,
        Category::FIELD_SLUG,
        Category::FIELD_SORT_ORDER,
        Category::FIELD_UPDATED_AT,
    ];

    /** @var array<string, string> */
    private const array SORT_COLUMNS = [
        Category::FIELD_ID => Category::TABLE_NAME . '.' . Category::FIELD_ID,
        Category::FIELD_NAME => Category::TABLE_NAME . '.' . Category::FIELD_NAME,
        Category::FIELD_SLUG => Category::TABLE_NAME . '.' . Category::FIELD_SLUG,
        Category::FIELD_SORT_ORDER => Category::TABLE_NAME . '.' . Category::FIELD_SORT_ORDER,
        Category::FIELD_UPDATED_AT => Category::TABLE_NAME . '.' . Category::FIELD_UPDATED_AT,
    ];

    public function __construct(
        private readonly Category $category,
        private readonly SlugService $slugs,
    ) {}

    /** @return LengthAwarePaginator<int, Category> */
    public function paginate(int $perPage, SortQuery $sort, CategoryListFilter $filter): LengthAwarePaginator
    {
        $query = $this->category->newQuery();
        $this->applyFilter($query, $filter);
        $query->orderBy(self::SORT_COLUMNS[$sort->column], $sort->direction);

        if ($sort->column !== Category::FIELD_ID) {
            $query->orderByDesc(Category::TABLE_NAME . '.' . Category::FIELD_ID);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @param Builder<Category> $query */
    private function applyFilter(Builder $query, CategoryListFilter $filter): void
    {
        if ($filter->name !== null) {
            $like = '%' . addcslashes($filter->name, '%_\\') . '%';
            $query->where(Category::TABLE_NAME . '.' . Category::FIELD_NAME, 'like', $like);
        }

        if ($filter->slug !== null) {
            $like = '%' . addcslashes($filter->slug, '%_\\') . '%';
            $query->where(Category::TABLE_NAME . '.' . Category::FIELD_SLUG, 'like', $like);
        }

        if ($filter->sortOrder !== null) {
            $query->where(Category::TABLE_NAME . '.' . Category::FIELD_SORT_ORDER, $filter->sortOrder);
        }

        if ($filter->updatedAt !== null) {
            $query->whereDate(Category::TABLE_NAME . '.' . Category::FIELD_UPDATED_AT, $filter->updatedAt);
        }
    }

    /** @return Collection<int, Category> */
    public function all(): Collection
    {
        /** @var Collection<int, Category> */
        return $this->category->newQuery()
            ->orderBy(Category::FIELD_SORT_ORDER)
            ->orderBy(Category::FIELD_NAME)
            ->get();
    }

    public function count(): int
    {
        return $this->category->newQuery()->count();
    }

    public function findOrFail(int $id): Category
    {
        $category = $this->category->newQuery()->whereKey($id)->first();
        if ($category === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $category;
    }

    public function create(StoreCategoryDto $dto): Category
    {
        $category = $this->category->newInstance();
        $this->fill($category, $dto);
        $category->save();

        return $category;
    }

    public function update(Category $category, UpdateCategoryDto $dto): Category
    {
        $this->fill($category, $dto);
        $category->save();

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    private function fill(Category $category, StoreCategoryDto|UpdateCategoryDto $dto): void
    {
        $ignoreId = $category->exists ? $category->getId() : null;
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $this->slugs->unique(
            $slugSource !== '' ? $slugSource : $dto->name,
            fn(string $candidate): bool => $this->slugExists($candidate, $ignoreId),
        );

        $category->fill([
            Category::FIELD_NAME => $dto->name,
            Category::FIELD_SLUG => $slug,
            Category::FIELD_DESCRIPTION => $dto->description !== null && trim($dto->description) !== ''
                ? $dto->description
                : null,
            Category::FIELD_SORT_ORDER => $dto->sort_order ?? 0,
        ]);
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = $this->category->newQuery()->where(Category::FIELD_SLUG, $slug);
        if ($ignoreId !== null) {
            $query->where(Category::FIELD_ID, '!=', $ignoreId);
        }

        return $query->exists();
    }
}

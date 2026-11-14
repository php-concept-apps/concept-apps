<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Category\StoreCategoryDto;
use Concept\App\Dto\Category\UpdateCategoryDto;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Models\Category;
use Concept\App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class CategoryService
{
    private const string ERR_NOT_FOUND = 'Category not found.';

    /** @return LengthAwarePaginator<int, Category> */
    public function paginate(int $perPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Category> $categories */
        $categories = Category::query()
            ->orderBy(Category::FIELD_SORT_ORDER)
            ->orderBy(Category::FIELD_NAME)
            ->paginate($perPage)
            ->withQueryString();

        return $categories;
    }

    /** @return Collection<int, Category> */
    public function all(): Collection
    {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()
            ->orderBy(Category::FIELD_SORT_ORDER)
            ->orderBy(Category::FIELD_NAME)
            ->get();

        return $categories;
    }

    public function findOrFail(int $id): Category
    {
        /** @var Category|null $category */
        $category = Category::query()->whereKey($id)->first();
        if ($category === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $category;
    }

    public function create(StoreCategoryDto $dto): Category
    {
        $category = new Category();
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
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $slugSource !== '' ? Slug::from($slugSource) : Slug::from($dto->name);
        $slug = $this->uniqueSlug($slug, $category->exists ? $category->getId() : null);

        $category->fill([
            Category::FIELD_NAME => $dto->name,
            Category::FIELD_SLUG => $slug,
            Category::FIELD_DESCRIPTION => $dto->description !== null && trim($dto->description) !== ''
                ? $dto->description
                : null,
            Category::FIELD_SORT_ORDER => $dto->sort_order ?? 0,
        ]);
    }

    private function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $base = $slug;
        $i = 1;
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = Category::query()->where(Category::FIELD_SLUG, $slug);
        if ($ignoreId !== null) {
            $query->where(Category::FIELD_ID, '!=', $ignoreId);
        }

        return $query->exists();
    }
}

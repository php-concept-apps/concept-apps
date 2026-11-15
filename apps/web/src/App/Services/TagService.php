<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Tag\StoreTagDto;
use Concept\App\Dto\Tag\UpdateTagDto;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Listing\SortQuery;
use Concept\App\Listing\TagListFilter;
use Concept\App\Models\Tag;
use Concept\App\Slug\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class TagService
{
    private const string ERR_NOT_FOUND = 'Tag not found.';

    public const string SORT_DEFAULT = Tag::FIELD_ID;

    /** @var list<string> */
    public const array SORTABLE = [
        Tag::FIELD_ID,
        Tag::FIELD_NAME,
        Tag::FIELD_SLUG,
        Tag::FIELD_UPDATED_AT,
    ];

    /** @var array<string, string> */
    private const array SORT_COLUMNS = [
        Tag::FIELD_ID => Tag::TABLE_NAME . '.' . Tag::FIELD_ID,
        Tag::FIELD_NAME => Tag::TABLE_NAME . '.' . Tag::FIELD_NAME,
        Tag::FIELD_SLUG => Tag::TABLE_NAME . '.' . Tag::FIELD_SLUG,
        Tag::FIELD_UPDATED_AT => Tag::TABLE_NAME . '.' . Tag::FIELD_UPDATED_AT,
    ];

    public function __construct(
        private readonly Tag $tag,
        private readonly SlugService $slugs,
    ) {}

    /** @return LengthAwarePaginator<int, Tag> */
    public function paginate(int $perPage, SortQuery $sort, TagListFilter $filter): LengthAwarePaginator
    {
        $query = $this->tag->newQuery();
        $this->applyFilter($query, $filter);
        $query->orderBy(self::SORT_COLUMNS[$sort->column], $sort->direction);

        if ($sort->column !== Tag::FIELD_ID) {
            $query->orderByDesc(Tag::TABLE_NAME . '.' . Tag::FIELD_ID);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @param Builder<Tag> $query */
    private function applyFilter(Builder $query, TagListFilter $filter): void
    {
        if ($filter->name !== null) {
            $like = '%' . addcslashes($filter->name, '%_\\') . '%';
            $query->where(Tag::TABLE_NAME . '.' . Tag::FIELD_NAME, 'like', $like);
        }

        if ($filter->slug !== null) {
            $like = '%' . addcslashes($filter->slug, '%_\\') . '%';
            $query->where(Tag::TABLE_NAME . '.' . Tag::FIELD_SLUG, 'like', $like);
        }

        if ($filter->updatedAt !== null) {
            $query->whereDate(Tag::TABLE_NAME . '.' . Tag::FIELD_UPDATED_AT, $filter->updatedAt);
        }
    }

    /** @return Collection<int, Tag> */
    public function all(): Collection
    {
        /** @var Collection<int, Tag> */
        return $this->tag->newQuery()->orderBy(Tag::FIELD_NAME)->get();
    }

    public function count(): int
    {
        return $this->tag->newQuery()->count();
    }

    public function findOrFail(int $id): Tag
    {
        $tag = $this->tag->newQuery()->whereKey($id)->first();
        if ($tag === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $tag;
    }

    public function create(StoreTagDto $dto): Tag
    {
        $tag = $this->tag->newInstance();
        $this->fill($tag, $dto);
        $tag->save();

        return $tag;
    }

    public function update(Tag $tag, UpdateTagDto $dto): Tag
    {
        $this->fill($tag, $dto);
        $tag->save();

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }

    private function fill(Tag $tag, StoreTagDto|UpdateTagDto $dto): void
    {
        $ignoreId = $tag->exists ? $tag->getId() : null;
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $this->slugs->unique(
            $slugSource !== '' ? $slugSource : $dto->name,
            fn(string $candidate): bool => $this->slugExists($candidate, $ignoreId),
        );

        $tag->fill([
            Tag::FIELD_NAME => $dto->name,
            Tag::FIELD_SLUG => $slug,
        ]);
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = $this->tag->newQuery()->where(Tag::FIELD_SLUG, $slug);
        if ($ignoreId !== null) {
            $query->where(Tag::FIELD_ID, '!=', $ignoreId);
        }

        return $query->exists();
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Tag\StoreTagDto;
use Concept\App\Dto\Tag\UpdateTagDto;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Models\Tag;
use Concept\App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class TagService
{
    private const string ERR_NOT_FOUND = 'Tag not found.';

    /** @return LengthAwarePaginator<int, Tag> */
    public function paginate(int $perPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Tag> $tags */
        $tags = Tag::query()
            ->orderBy(Tag::FIELD_NAME)
            ->paginate($perPage)
            ->withQueryString();

        return $tags;
    }

    /** @return Collection<int, Tag> */
    public function all(): Collection
    {
        /** @var Collection<int, Tag> $tags */
        $tags = Tag::query()->orderBy(Tag::FIELD_NAME)->get();

        return $tags;
    }

    public function findOrFail(int $id): Tag
    {
        /** @var Tag|null $tag */
        $tag = Tag::query()->whereKey($id)->first();
        if ($tag === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $tag;
    }

    public function create(StoreTagDto $dto): Tag
    {
        $tag = new Tag();
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
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $slugSource !== '' ? Slug::from($slugSource) : Slug::from($dto->name);
        $slug = $this->uniqueSlug($slug, $tag->exists ? $tag->getId() : null);

        $tag->fill([
            Tag::FIELD_NAME => $dto->name,
            Tag::FIELD_SLUG => $slug,
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
        $query = Tag::query()->where(Tag::FIELD_SLUG, $slug);
        if ($ignoreId !== null) {
            $query->where(Tag::FIELD_ID, '!=', $ignoreId);
        }

        return $query->exists();
    }
}

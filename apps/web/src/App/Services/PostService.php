<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Dto\Post\UpdatePostDto;
use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Models\Post;
use Concept\App\Models\User;
use Concept\App\Support\Slug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PostService
{
    private const string ERR_NOT_FOUND = 'Post not found.';

    /** @return LengthAwarePaginator<int, Post> */
    public function paginate(int $perPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Post> $posts */
        $posts = Post::query()
            ->with(['category', 'author', 'tags'])
            ->orderByDesc(Post::FIELD_ID)
            ->paginate($perPage)
            ->withQueryString();

        return $posts;
    }

    public function findOrFail(int $id): Post
    {
        /** @var Post|null $post */
        $post = Post::query()->with('tags')->whereKey($id)->first();
        if ($post === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $post;
    }

    public function create(StorePostDto $dto, User $author): Post
    {
        $post = new Post();
        $this->fill($post, $dto);
        $post->setAttribute(Post::FIELD_USER_ID, $author->getId());
        $post->save();
        $post->tags()->sync($this->normalizeTagIds($dto->tag_ids ?? []));

        return $post;
    }

    public function update(Post $post, UpdatePostDto $dto): Post
    {
        $this->fill($post, $dto);
        $post->save();
        $post->tags()->sync($this->normalizeTagIds($dto->tag_ids ?? []));

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    private function fill(Post $post, StorePostDto|UpdatePostDto $dto): void
    {
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $slugSource !== '' ? Slug::from($slugSource) : Slug::from($dto->title);
        $slug = $this->uniqueSlug($slug, $post->exists ? $post->getId() : null);

        $status = PostStatus::tryFrom($dto->status) ?? PostStatus::Draft;
        $categoryId = $dto->category_id !== null && $dto->category_id > 0
            ? $dto->category_id
            : null;

        $publishedAt = $dto->published_at !== null && trim($dto->published_at) !== ''
            ? $dto->published_at
            : null;
        if ($status === PostStatus::Published && $publishedAt === null) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $post->fill([
            Post::FIELD_TITLE => $dto->title,
            Post::FIELD_SLUG => $slug,
            Post::FIELD_EXCERPT => $dto->excerpt !== null && trim($dto->excerpt) !== '' ? $dto->excerpt : null,
            Post::FIELD_BODY => $dto->body,
            Post::FIELD_CATEGORY_ID => $categoryId,
            Post::FIELD_STATUS => $status,
            Post::FIELD_PUBLISHED_AT => $publishedAt,
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
        $query = Post::query()->where(Post::FIELD_SLUG, $slug);
        if ($ignoreId !== null) {
            $query->where(Post::FIELD_ID, '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * @param list<int>|null $tagIds
     * @return list<int>
     */
    private function normalizeTagIds(?array $tagIds): array
    {
        if ($tagIds === null || $tagIds === []) {
            return [];
        }

        return array_values(array_unique($tagIds));
    }
}

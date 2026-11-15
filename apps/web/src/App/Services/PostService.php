<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Dto\Post\UpdatePostDto;
use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Listing\PostListFilter;
use Concept\App\Listing\SortQuery;
use Concept\App\Models\Category;
use Concept\App\Models\Post;
use Concept\App\Models\Tag;
use Concept\App\Models\User;
use Concept\App\Slug\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PostService
{
    private const string ERR_NOT_FOUND = 'Post not found.';

    public const string SORT_DEFAULT = Post::FIELD_ID;

    /** @var list<string> */
    public const array SORTABLE = [
        Post::FIELD_ID,
        Post::FIELD_TITLE,
        Post::RELATION_CATEGORY,
        Post::FIELD_STATUS,
        Post::FIELD_PUBLISHED_AT,
        Post::FIELD_UPDATED_AT,
    ];

    /** @var array<string, string> */
    private const array SORT_COLUMNS = [
        Post::FIELD_ID => Post::TABLE_NAME . '.' . Post::FIELD_ID,
        Post::FIELD_TITLE => Post::TABLE_NAME . '.' . Post::FIELD_TITLE,
        Post::RELATION_CATEGORY => Category::TABLE_NAME . '.' . Category::FIELD_NAME,
        Post::FIELD_STATUS => Post::TABLE_NAME . '.' . Post::FIELD_STATUS,
        Post::FIELD_PUBLISHED_AT => Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT,
        Post::FIELD_UPDATED_AT => Post::TABLE_NAME . '.' . Post::FIELD_UPDATED_AT,
    ];

    public function __construct(
        private readonly Post $post,
        private readonly SlugService $slugs,
    ) {}

    /** @return LengthAwarePaginator<int, Post> */
    public function paginate(int $perPage, SortQuery $sort, PostListFilter $filter): LengthAwarePaginator
    {
        $query = $this->post->newQuery()->with([
            Post::RELATION_CATEGORY,
            Post::RELATION_AUTHOR,
            Post::RELATION_TAGS,
        ]);

        $this->applyFilter($query, $filter);

        if ($sort->column === Post::RELATION_CATEGORY) {
            $query
                ->leftJoin(
                    Category::TABLE_NAME,
                    Post::TABLE_NAME . '.' . Post::FIELD_CATEGORY_ID,
                    '=',
                    Category::TABLE_NAME . '.' . Category::FIELD_ID,
                )
                ->select(Post::TABLE_NAME . '.*');
        }

        $query->orderBy(self::SORT_COLUMNS[$sort->column], $sort->direction);

        if ($sort->column !== Post::FIELD_ID) {
            $query->orderByDesc(Post::TABLE_NAME . '.' . Post::FIELD_ID);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @param Builder<Post> $query */
    private function applyFilter(Builder $query, PostListFilter $filter): void
    {
        if ($filter->search !== null) {
            $like = '%' . addcslashes($filter->search, '%_\\') . '%';
            $query->where(static function(Builder $inner) use ($like): void {
                $inner
                    ->where(Post::TABLE_NAME . '.' . Post::FIELD_TITLE, 'like', $like)
                    ->orWhere(Post::TABLE_NAME . '.' . Post::FIELD_SLUG, 'like', $like);
            });
        }

        if ($filter->status !== null) {
            $query->where(Post::TABLE_NAME . '.' . Post::FIELD_STATUS, $filter->status->value);
        }

        if ($filter->categoryId !== null) {
            $query->where(Post::TABLE_NAME . '.' . Post::FIELD_CATEGORY_ID, $filter->categoryId);
        }

        if ($filter->tagId !== null) {
            $tagId = $filter->tagId;
            $query->whereHas(
                Post::RELATION_TAGS,
                static function(Builder $tags) use ($tagId): void {
                    $tags->where(Tag::TABLE_NAME . '.' . Tag::FIELD_ID, $tagId);
                },
            );
        }

        if ($filter->publishedAt !== null) {
            $query->whereDate(
                Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT,
                $filter->publishedAt,
            );
        }

        if ($filter->updatedAt !== null) {
            $query->whereDate(
                Post::TABLE_NAME . '.' . Post::FIELD_UPDATED_AT,
                $filter->updatedAt,
            );
        }
    }

    public function count(): int
    {
        return $this->post->newQuery()->count();
    }

    public function findOrFail(int $id): Post
    {
        $post = $this->post->newQuery()->with(Post::RELATION_TAGS)->whereKey($id)->first();
        if ($post === null) {
            throw new ModelNotFoundException(self::ERR_NOT_FOUND);
        }

        return $post;
    }

    public function create(StorePostDto $dto, User $author): Post
    {
        $post = $this->post->newInstance();
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
        $ignoreId = $post->exists ? $post->getId() : null;
        $slugSource = is_string($dto->slug) ? trim($dto->slug) : '';
        $slug = $this->slugs->unique(
            $slugSource !== '' ? $slugSource : $dto->title,
            fn(string $candidate): bool => $this->slugExists($candidate, $ignoreId),
        );

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

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = $this->post->newQuery()->where(Post::FIELD_SLUG, $slug);
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

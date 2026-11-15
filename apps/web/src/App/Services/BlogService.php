<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Models\Category;
use Concept\App\Models\Post;
use Concept\App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class BlogService
{
    private const string ERR_POST_NOT_FOUND = 'Post not found.';
    private const string ERR_CATEGORY_NOT_FOUND = 'Category not found.';
    private const string ERR_TAG_NOT_FOUND = 'Tag not found.';

    private const int RECENT_LIMIT = 5;

    public function __construct(
        private readonly Post $post,
        private readonly Category $category,
        private readonly Tag $tag,
    ) {}

    /** @return LengthAwarePaginator<int, Post> */
    public function paginatePublished(int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->publishedQuery()->with([
            Post::RELATION_CATEGORY,
            Post::RELATION_AUTHOR,
            Post::RELATION_TAGS,
        ]);

        $this->applySearch($query, $search);

        return $query
            ->orderByDesc(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return LengthAwarePaginator<int, Post> */
    public function paginateByCategorySlug(string $slug, int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $category = $this->findCategoryBySlugOrFail($slug);
        $query = $this->publishedQuery()
            ->with([Post::RELATION_CATEGORY, Post::RELATION_AUTHOR, Post::RELATION_TAGS])
            ->where(Post::TABLE_NAME . '.' . Post::FIELD_CATEGORY_ID, $category->getId());

        $this->applySearch($query, $search);

        return $query
            ->orderByDesc(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return LengthAwarePaginator<int, Post> */
    public function paginateByTagSlug(string $slug, int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $tag = $this->findTagBySlugOrFail($slug);
        $tagId = $tag->getId();
        $query = $this->publishedQuery()
            ->with([Post::RELATION_CATEGORY, Post::RELATION_AUTHOR, Post::RELATION_TAGS])
            ->whereHas(
                Post::RELATION_TAGS,
                static function(Builder $tags) use ($tagId): void {
                    $tags->where(Tag::TABLE_NAME . '.' . Tag::FIELD_ID, $tagId);
                },
            );

        $this->applySearch($query, $search);

        return $query
            ->orderByDesc(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlugOrFail(string $slug): Post
    {
        $post = $this->publishedQuery()
            ->with([Post::RELATION_CATEGORY, Post::RELATION_AUTHOR, Post::RELATION_TAGS])
            ->where(Post::TABLE_NAME . '.' . Post::FIELD_SLUG, $slug)
            ->first();

        if ($post === null) {
            throw new ModelNotFoundException(self::ERR_POST_NOT_FOUND);
        }

        return $post;
    }

    public function findCategoryBySlugOrFail(string $slug): Category
    {
        $category = $this->category->newQuery()
            ->where(Category::FIELD_SLUG, $slug)
            ->first();

        if ($category === null) {
            throw new ModelNotFoundException(self::ERR_CATEGORY_NOT_FOUND);
        }

        return $category;
    }

    public function findTagBySlugOrFail(string $slug): Tag
    {
        $tag = $this->tag->newQuery()
            ->where(Tag::FIELD_SLUG, $slug)
            ->first();

        if ($tag === null) {
            throw new ModelNotFoundException(self::ERR_TAG_NOT_FOUND);
        }

        return $tag;
    }

    /** @return Collection<int, Post> */
    public function recentPublished(int $limit = self::RECENT_LIMIT): Collection
    {
        /** @var Collection<int, Post> */
        return $this->publishedQuery()
            ->orderByDesc(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT)
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Category> */
    public function categoriesWithPublishedCount(): Collection
    {
        /** @var Collection<int, Category> */
        return $this->category->newQuery()
            ->withCount([Category::RELATION_POSTS . ' as published_posts_count' => function(Builder $query): void {
                $this->constrainPublished($query);
            }])
            ->orderBy(Category::FIELD_SORT_ORDER)
            ->orderBy(Category::FIELD_NAME)
            ->get();
    }

    /** @return Collection<int, Tag> */
    public function tagsInUse(): Collection
    {
        /** @var Collection<int, Tag> */
        return $this->tag->newQuery()
            ->whereHas(Tag::RELATION_POSTS, function(Builder $query): void {
                $this->constrainPublished($query);
            })
            ->orderBy(Tag::FIELD_NAME)
            ->get();
    }

    /** @param Builder<Post> $query */
    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $like = '%' . addcslashes($search, '%_\\') . '%';
        $query->where(static function(Builder $inner) use ($like): void {
            $inner
                ->where(Post::TABLE_NAME . '.' . Post::FIELD_TITLE, 'like', $like)
                ->orWhere(Post::TABLE_NAME . '.' . Post::FIELD_EXCERPT, 'like', $like);
        });
    }

    /** @return Builder<Post> */
    private function publishedQuery(): Builder
    {
        $query = $this->post->newQuery();
        $this->constrainPublished($query);

        return $query;
    }

    /** @param Builder<covariant Model> $query */
    private function constrainPublished(Builder $query): void
    {
        $query
            ->where(Post::TABLE_NAME . '.' . Post::FIELD_STATUS, PostStatus::Published->value)
            ->whereNotNull(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT)
            ->where(Post::TABLE_NAME . '.' . Post::FIELD_PUBLISHED_AT, '<=', date('Y-m-d H:i:s'));
    }
}

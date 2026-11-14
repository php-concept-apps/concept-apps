<?php declare(strict_types=1);

namespace Concept\App\Services;

use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Dto\Post\UpdatePostDto;
use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\ModelNotFoundException;
use Concept\App\Http\Listing\SortQuery;
use Concept\App\Models\Category;
use Concept\App\Models\Post;
use Concept\App\Models\User;
use Concept\App\Slug\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PostService
{
    private const string ERR_NOT_FOUND = 'Post not found.';

    public const string SORT_DEFAULT = Post::FIELD_ID;

    /** @var list<string> */
    public const array SORTABLE = [
        Post::FIELD_ID,
        Post::FIELD_TITLE,
        'category',
        Post::FIELD_STATUS,
        Post::FIELD_PUBLISHED_AT,
        Post::FIELD_UPDATED_AT,
    ];

    /** @var array<string, string> */
    private const array SORT_COLUMNS = [
        Post::FIELD_ID => 'posts.' . Post::FIELD_ID,
        Post::FIELD_TITLE => 'posts.' . Post::FIELD_TITLE,
        'category' => 'categories.' . Category::FIELD_NAME,
        Post::FIELD_STATUS => 'posts.' . Post::FIELD_STATUS,
        Post::FIELD_PUBLISHED_AT => 'posts.' . Post::FIELD_PUBLISHED_AT,
        Post::FIELD_UPDATED_AT => 'posts.' . Post::FIELD_UPDATED_AT,
    ];

    public function __construct(
        private readonly Post $post,
        private readonly SlugService $slugs,
    ) {}

    /** @return LengthAwarePaginator<int, Post> */
    public function paginate(int $perPage, SortQuery $sort): LengthAwarePaginator
    {
        $query = $this->post->newQuery()->with(['category', 'author', 'tags']);

        if ($sort->column === 'category') {
            $query
                ->leftJoin(
                    Category::TABLE_NAME,
                    'posts.' . Post::FIELD_CATEGORY_ID,
                    '=',
                    'categories.' . Category::FIELD_ID,
                )
                ->select('posts.*');
        }

        $query->orderBy(self::SORT_COLUMNS[$sort->column], $sort->direction);

        if ($sort->column !== Post::FIELD_ID) {
            $query->orderByDesc('posts.' . Post::FIELD_ID);
        }

        /** @var LengthAwarePaginator<int, Post> $posts */
        $posts = $query->paginate($perPage)->withQueryString();

        return $posts;
    }

    public function findOrFail(int $id): Post
    {
        $post = $this->post->newQuery()->with('tags')->whereKey($id)->first();
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

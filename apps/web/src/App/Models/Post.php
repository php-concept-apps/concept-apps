<?php declare(strict_types=1);

namespace Concept\App\Models;

use Concept\App\Enums\PostStatus;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Post extends Model
{
    use SoftDeletes;

    public const string TABLE_NAME = 'posts';

    public const string FIELD_ID = 'id';
    public const string FIELD_USER_ID = 'user_id';
    public const string FIELD_CATEGORY_ID = 'category_id';
    public const string FIELD_TITLE = 'title';
    public const string FIELD_SLUG = 'slug';
    public const string FIELD_EXCERPT = 'excerpt';
    public const string FIELD_BODY = 'body';
    public const string FIELD_COVER_IMAGE = 'cover_image';
    public const string FIELD_STATUS = 'status';
    public const string FIELD_PUBLISHED_AT = 'published_at';
    public const string FIELD_CREATED_AT = 'created_at';
    public const string FIELD_UPDATED_AT = 'updated_at';
    public const string FIELD_DELETED_AT = 'deleted_at';

    public const string RELATION_AUTHOR = 'author';
    public const string RELATION_CATEGORY = 'category';
    public const string RELATION_TAGS = 'tags';

    public const string INPUT_TAG_IDS = 'tag_ids';

    protected $table = self::TABLE_NAME;

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_USER_ID,
        self::FIELD_CATEGORY_ID,
        self::FIELD_TITLE,
        self::FIELD_SLUG,
        self::FIELD_EXCERPT,
        self::FIELD_BODY,
        self::FIELD_COVER_IMAGE,
        self::FIELD_STATUS,
        self::FIELD_PUBLISHED_AT,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            self::FIELD_STATUS => PostStatus::class,
            self::FIELD_PUBLISHED_AT => 'datetime',
        ];
    }

    public function getId(): int
    {
        $id = $this->getAttribute(self::FIELD_ID);

        return is_numeric($id) ? (int) $id : 0;
    }

    public function getUserId(): int
    {
        $userId = $this->getAttribute(self::FIELD_USER_ID);

        return is_numeric($userId) ? (int) $userId : 0;
    }

    public function getCategoryId(): ?int
    {
        $categoryId = $this->getAttribute(self::FIELD_CATEGORY_ID);

        return is_numeric($categoryId) ? (int) $categoryId : null;
    }

    public function getTitle(): string
    {
        $title = $this->getAttribute(self::FIELD_TITLE);

        return is_string($title) ? $title : '';
    }

    public function getSlug(): string
    {
        $slug = $this->getAttribute(self::FIELD_SLUG);

        return is_string($slug) ? $slug : '';
    }

    public function getExcerpt(): ?string
    {
        $excerpt = $this->getAttribute(self::FIELD_EXCERPT);

        return is_string($excerpt) ? $excerpt : null;
    }

    public function getBody(): string
    {
        $body = $this->getAttribute(self::FIELD_BODY);

        return is_string($body) ? $body : '';
    }

    public function getCoverImage(): ?string
    {
        $cover = $this->getAttribute(self::FIELD_COVER_IMAGE);

        return is_string($cover) ? $cover : null;
    }

    public function getStatus(): ?PostStatus
    {
        $status = $this->getAttribute(self::FIELD_STATUS);

        return $status instanceof PostStatus ? $status : null;
    }

    public function getPublishedAt(): ?DateTimeInterface
    {
        $publishedAt = $this->getAttribute(self::FIELD_PUBLISHED_AT);

        return $publishedAt instanceof DateTimeInterface ? $publishedAt : null;
    }

    public function isPublished(): bool
    {
        return $this->getStatus() === PostStatus::Published;
    }

    public function isDraft(): bool
    {
        return $this->getStatus() === PostStatus::Draft;
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, self::FIELD_USER_ID);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, self::FIELD_CATEGORY_ID);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}

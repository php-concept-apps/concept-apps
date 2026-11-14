<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

use Concept\App\Enums\PostStatus;
use Concept\App\Models\Post;
use DateTimeImmutable;

final class PostListFilter
{
    public const string PARAM_SEARCH = 'q';
    public const string PARAM_TAG_ID = 'tag_id';

    public function __construct(
        public readonly ?string $search = null,
        public readonly ?PostStatus $status = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $tagId = null,
        public readonly ?string $publishedAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    /**
     * @param array<array-key, mixed> $query
     */
    public static function from(array $query): self
    {
        $search = is_string($query[self::PARAM_SEARCH] ?? null)
            ? trim($query[self::PARAM_SEARCH])
            : '';
        $search = $search !== '' ? $search : null;

        $statusRaw = is_string($query[Post::FIELD_STATUS] ?? null)
            ? $query[Post::FIELD_STATUS]
            : null;
        $status = is_string($statusRaw) ? PostStatus::tryFrom($statusRaw) : null;

        return new self(
            $search,
            $status,
            self::positiveInt($query[Post::FIELD_CATEGORY_ID] ?? null),
            self::positiveInt($query[self::PARAM_TAG_ID] ?? null),
            self::date($query[Post::FIELD_PUBLISHED_AT] ?? null),
            self::date($query[Post::FIELD_UPDATED_AT] ?? null),
        );
    }

    public function isActive(): bool
    {
        return $this->search !== null
            || $this->status !== null
            || $this->categoryId !== null
            || $this->tagId !== null
            || $this->publishedAt !== null
            || $this->updatedAt !== null;
    }

    /**
     * Query params to preserve in sort / pagination links (without sort/dir).
     *
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->search !== null) {
            $params[self::PARAM_SEARCH] = $this->search;
        }
        if ($this->status !== null) {
            $params[Post::FIELD_STATUS] = $this->status->value;
        }
        if ($this->categoryId !== null) {
            $params[Post::FIELD_CATEGORY_ID] = (string) $this->categoryId;
        }
        if ($this->tagId !== null) {
            $params[self::PARAM_TAG_ID] = (string) $this->tagId;
        }
        if ($this->publishedAt !== null) {
            $params[Post::FIELD_PUBLISHED_AT] = $this->publishedAt;
        }
        if ($this->updatedAt !== null) {
            $params[Post::FIELD_UPDATED_AT] = $this->updatedAt;
        }

        return $params;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return null;
    }

    private static function date(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }
}

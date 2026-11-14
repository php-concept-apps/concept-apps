<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

use Concept\App\Enums\PostStatus;
use Concept\App\Models\Post;

final class PostListFilter
{
    public const string PARAM_SEARCH = 'q';
    public const string PARAM_TAG_ID = 'tag_id';

    public function __construct(
        public readonly ?string $search = null,
        public readonly ?PostStatus $status = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $tagId = null,
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
        );
    }

    public function isActive(): bool
    {
        return $this->search !== null
            || $this->status !== null
            || $this->categoryId !== null
            || $this->tagId !== null;
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
}

<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

use Concept\App\Models\Tag;

final class TagListFilter
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $updatedAt = null,
    ) {}

    /**
     * @param array<array-key, mixed> $query
     */
    public static function from(array $query): self
    {
        return new self(
            ListFilterSupport::optionalString($query[Tag::FIELD_NAME] ?? null),
            ListFilterSupport::optionalString($query[Tag::FIELD_SLUG] ?? null),
            ListFilterSupport::optionalDate($query[Tag::FIELD_UPDATED_AT] ?? null),
        );
    }

    public function isActive(): bool
    {
        return $this->name !== null
            || $this->slug !== null
            || $this->updatedAt !== null;
    }

    /**
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->name !== null) {
            $params[Tag::FIELD_NAME] = $this->name;
        }
        if ($this->slug !== null) {
            $params[Tag::FIELD_SLUG] = $this->slug;
        }
        if ($this->updatedAt !== null) {
            $params[Tag::FIELD_UPDATED_AT] = $this->updatedAt;
        }

        return $params;
    }
}

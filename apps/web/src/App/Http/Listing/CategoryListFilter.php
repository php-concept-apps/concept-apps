<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

use Concept\App\Models\Category;

final class CategoryListFilter
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?int $sortOrder = null,
        public readonly ?string $updatedAt = null,
    ) {}

    /**
     * @param array<array-key, mixed> $query
     */
    public static function from(array $query): self
    {
        return new self(
            ListFilterSupport::optionalString($query[Category::FIELD_NAME] ?? null),
            ListFilterSupport::optionalString($query[Category::FIELD_SLUG] ?? null),
            ListFilterSupport::optionalInt($query[Category::FIELD_SORT_ORDER] ?? null),
            ListFilterSupport::optionalDate($query[Category::FIELD_UPDATED_AT] ?? null),
        );
    }

    public function isActive(): bool
    {
        return $this->name !== null
            || $this->slug !== null
            || $this->sortOrder !== null
            || $this->updatedAt !== null;
    }

    /**
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->name !== null) {
            $params[Category::FIELD_NAME] = $this->name;
        }
        if ($this->slug !== null) {
            $params[Category::FIELD_SLUG] = $this->slug;
        }
        if ($this->sortOrder !== null) {
            $params[Category::FIELD_SORT_ORDER] = (string) $this->sortOrder;
        }
        if ($this->updatedAt !== null) {
            $params[Category::FIELD_UPDATED_AT] = $this->updatedAt;
        }

        return $params;
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Dto\Post;

use Concept\Extensions\CastingValinor\Contracts\DtoInterface;
use Concept\Extensions\CastingValinor\Dto\Dto;

final class StorePostDto extends Dto implements DtoInterface
{
    /**
     * @param non-empty-list<int>|null $tag_ids
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $status,
        public readonly ?string $slug = null,
        public readonly ?string $excerpt = null,
        public readonly ?int $category_id = null,
        public readonly ?string $published_at = null,
        public readonly ?array $tag_ids = null,
    ) {}
}

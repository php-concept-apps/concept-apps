<?php declare(strict_types=1);

namespace Concept\App\Dto\Post;

use Concept\Extensions\CastingValinor\Contracts\DtoInterface;
use Concept\Extensions\CastingValinor\Dto\Dto;

final class UpdatePostDto extends Dto implements DtoInterface
{
    /**
     * @param list<int|string> $tag_ids
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $status,
        public readonly string $slug = '',
        public readonly ?string $excerpt = null,
        public readonly int|string|null $category_id = null,
        public readonly ?string $published_at = null,
        public readonly array $tag_ids = [],
    ) {}
}

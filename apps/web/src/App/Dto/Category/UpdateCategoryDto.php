<?php declare(strict_types=1);

namespace Concept\App\Dto\Category;

use Concept\Extensions\CastingValinor\Contracts\DtoInterface;
use Concept\Extensions\CastingValinor\Dto\Dto;

final class UpdateCategoryDto extends Dto implements DtoInterface
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug = '',
        public readonly ?string $description = null,
        public readonly int|string $sort_order = 0,
    ) {}
}

<?php declare(strict_types=1);

namespace Concept\App\Dto\Tag;

use Concept\Extensions\CastingValinor\Contracts\DtoInterface;
use Concept\Extensions\CastingValinor\Dto\Dto;

final class StoreTagDto extends Dto implements DtoInterface
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
    ) {}
}

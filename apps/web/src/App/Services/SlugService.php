<?php declare(strict_types=1);

namespace Concept\App\Services;

use Cocur\Slugify\SlugifyInterface;

final class SlugService
{
    public function __construct(
        private readonly SlugifyInterface $slugify,
    ) {}

    public function from(string $value): string
    {
        $slug = $this->slugify->slugify($value);

        return $slug !== '' ? $slug : 'item';
    }

    /**
     * Normalize source (custom slug or name/title); append -1, -2, … only when taken.
     *
     * @param callable(string): bool $exists
     */
    public function unique(string $value, callable $exists): string
    {
        $base = $this->from($value);
        $slug = $base;
        $i = 1;

        while ($exists($slug)) {
            $slug = $base . '-' . $i;
            ++$i;
        }

        return $slug;
    }
}

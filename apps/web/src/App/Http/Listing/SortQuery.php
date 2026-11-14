<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

final class SortQuery
{
    public const string ASC = 'asc';
    public const string DESC = 'desc';

    /**
     * @param self::ASC|self::DESC $direction
     */
    public function __construct(
        public readonly string $column,
        public readonly string $direction,
    ) {}

    /**
     * @param array<array-key, mixed> $query
     * @param list<string> $allowed
     * @param self::ASC|self::DESC $defaultDirection
     */
    public static function from(
        array $query,
        array $allowed,
        string $defaultColumn,
        string $defaultDirection = self::DESC,
    ): self {
        $column = is_string($query['sort'] ?? null) ? $query['sort'] : $defaultColumn;
        if (!in_array($column, $allowed, true)) {
            $column = $defaultColumn;
        }

        $rawDirection = is_string($query['dir'] ?? null) ? strtolower($query['dir']) : $defaultDirection;
        $direction = $rawDirection === self::ASC ? self::ASC : self::DESC;
        if ($rawDirection !== self::ASC && $rawDirection !== self::DESC) {
            $direction = $defaultDirection;
        }

        return new self($column, $direction);
    }

    public function nextDirectionFor(string $column): string
    {
        if ($this->column !== $column) {
            return self::ASC;
        }

        return $this->direction === self::ASC ? self::DESC : self::ASC;
    }

    public function is(string $column): bool
    {
        return $this->column === $column;
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Http\Listing;

use DateTimeImmutable;

final class ListFilterSupport
{
    public static function optionalString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    public static function optionalDate(mixed $value): ?string
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

    public static function optionalInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '' && preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }

    public static function optionalPositiveInt(mixed $value): ?int
    {
        $int = self::optionalInt($value);

        return $int !== null && $int > 0 ? $int : null;
    }
}

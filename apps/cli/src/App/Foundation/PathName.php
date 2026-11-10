<?php declare(strict_types=1);

namespace Concept\App\Foundation;

final class PathName
{
    public const string CONFIG = 'config';
    public const string LOGS = 'storage/logs';
    public const string CACHE = 'storage/cache';

    /**
     * @return array<string, string>
     */
    public static function map(): array
    {
        $paths = [
            self::CONFIG,
            self::LOGS,
            self::CACHE,
        ];

        return array_combine($paths, $paths);
    }
}

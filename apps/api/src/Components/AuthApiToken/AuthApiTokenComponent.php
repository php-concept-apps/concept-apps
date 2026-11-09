<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken;

use Concept\Components\AuthApiToken\Commands\CreateUserCommand;
use Concept\Extensions\Components\Contracts\ComponentInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Bearer API token authentication (opaque hashed tokens). App product scaffold.
 */
final class AuthApiTokenComponent implements ComponentInterface
{
    private const string NAME = 'AuthApiToken';
    private const string VERSION = '1.0.0';
    private const string DESCRIPTION = 'Bearer API token auth (login/me/logout, hashed api_tokens).';

    /** @var list<class-string<Command>> */
    private const array COMMANDS = [
        CreateUserCommand::class,
    ];

    private string $componentDir = __DIR__;

    public function name(): string
    {
        return self::NAME;
    }

    public function version(): string
    {
        return self::VERSION;
    }

    public function description(): string
    {
        return self::DESCRIPTION;
    }

    public function routes(): ?string
    {
        return null;
    }

    public function providers(): array
    {
        return [];
    }

    public function seeders(): array
    {
        return [];
    }

    public function migrationPaths(): array
    {
        return [
            $this->componentDir . '/Database/Migrations',
        ];
    }

    public function commands(): array
    {
        return self::COMMANDS;
    }

    public function viewExtensions(): array
    {
        return [];
    }

    public function viewPaths(): array
    {
        return [];
    }

    public function viewRouteNamespace(): array
    {
        return [];
    }

    public function assets(): array
    {
        return [];
    }
}

<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Exceptions;

use RuntimeException;

final class UnauthorizedException extends RuntimeException
{
    private const string ERR_UNAUTHORIZED = 'Unauthorized';

    public function __construct(string $message = self::ERR_UNAUTHORIZED)
    {
        parent::__construct($message);
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';
}

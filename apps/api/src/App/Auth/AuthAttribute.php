<?php declare(strict_types=1);

namespace Concept\App\Auth;

/**
 * PSR-7 request attributes set by bearer auth middleware.
 */
final class AuthAttribute
{
    public const string USER = 'auth.user';
    public const string TOKEN = 'auth.token';
}

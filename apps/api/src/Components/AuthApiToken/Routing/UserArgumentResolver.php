<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Routing;

use Concept\Components\AuthApiToken\Models\User;
use Concept\Components\AuthApiToken\Support\AuthAttribute;
use Concept\Core\Http\Contracts\ArgumentResolverInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Injects User from request attribute set by AuthenticateBearerMiddleware.
 * Wired via HttpBrick::appendResolvers() — after the built-in chain.
 */
final class UserArgumentResolver implements ArgumentResolverInterface
{
    public function supports(ReflectionParameter $parameter, array $vars): bool
    {
        $type = $parameter->getType();
        if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) {
            return false;
        }

        return $type->getName() === User::class;
    }

    public function resolve(ReflectionParameter $parameter, ServerRequestInterface $request, array $vars): mixed
    {
        $user = $request->getAttribute(AuthAttribute::USER);

        return $user instanceof User ? $user : null;
    }
}

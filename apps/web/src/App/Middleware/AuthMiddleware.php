<?php declare(strict_types=1);

namespace Concept\App\Middleware;

use Concept\App\Auth\AuthService;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    private const string MSG_LOGIN_REQUIRED = 'Please sign in to continue.';

    public function __construct(
        private readonly AuthService $auth,
        private readonly FlashBagInterface $flashBag,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->auth->check()) {
            return $handler->handle($request);
        }

        $this->flashBag->addError(self::MSG_LOGIN_REQUIRED);

        return $this->responseFactory->redirectByName('admin.login');
    }
}

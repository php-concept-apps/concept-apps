<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\App\Auth\AuthService;
use Concept\App\Dto\LoginDto;
use Concept\App\Exceptions\DtoException;
use Concept\App\Http\Requests\LoginRequest;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class AuthController
{
    private const string MSG_WELCOME = 'Welcome back, %s.';
    private const string MSG_LOGOUT = 'You have been logged out.';
    private const string MSG_AUTH_ERROR = 'Incorrect email or password.';
    private const string ERR_LOGIN_DTO = 'LoginRequest did not produce a LoginDto.';

    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly ResponseFactoryInterface $response,
        private readonly FlashBagInterface $flashBag,
        private readonly AuthService $auth,
    ) {}

    public function showLogin(): ResponseInterface
    {
        return $this->view->create('@dashboard/auth/login');
    }

    public function login(LoginRequest $request): ResponseInterface
    {
        $loginDto = $request->toDto();
        if (!$loginDto instanceof LoginDto) {
            throw new DtoException(self::ERR_LOGIN_DTO);
        }

        $user = $this->auth->attempt($loginDto->email, $loginDto->password, $loginDto->remember);
        if ($user !== null) {
            $this->flashBag->addSuccess(sprintf(self::MSG_WELCOME, $user->getName()));

            return $this->response->redirectByName('admin.home');
        }

        $this->flashBag->addError(self::MSG_AUTH_ERROR);

        return $this->response->redirectByName('admin.login');
    }

    public function logout(): ResponseInterface
    {
        $this->auth->logout();
        $this->flashBag->addInfo(self::MSG_LOGOUT);

        return $this->response->redirectByName('admin.login');
    }
}

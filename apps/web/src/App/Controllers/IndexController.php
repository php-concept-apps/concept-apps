<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Http\Requests\EchoRequest;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\Http\Contracts\UrlGeneratorInterface;
use Concept\Extensions\SessionSymfony\Contracts\FlashBagInterface;
use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class IndexController
{
    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
        private readonly ResponseFactoryInterface $response,
        private readonly FlashBagInterface $flashBag,
        private readonly UrlGeneratorInterface $url,
    ) {}

    public function index(): ResponseInterface
    {
        return $this->view->create('@frontend/index');
    }

    public function echo(EchoRequest $request): ResponseInterface
    {
        $validated = $request->validated();
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;

        $this->flashBag->addSuccess(sprintf(
            'Hello, %s — FormRequest validated %s and redirected back with flash.',
            is_string($name) ? $name : 'friend',
            is_string($email) ? $email : '',
        ));

        return $this->response->redirect($this->url->uri('home') . '#contact');
    }

    public function hello(string $name): ResponseInterface
    {
        return $this->view->create('@frontend/hello', [
            'name' => $name,
        ]);
    }
}

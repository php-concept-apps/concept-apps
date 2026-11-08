<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Http\Requests\EchoRequest;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class EchoController
{
    public function __construct(
        private readonly ResponseFactoryInterface $response,
    ) {}

    public function __invoke(EchoRequest $request): ResponseInterface
    {
        return $this->response->jsonSuccess([
            'received' => $request->validated(),
        ]);
    }
}

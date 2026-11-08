<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Http\Requests\EchoRequest;
use Psr\Http\Message\ResponseInterface;

final class EchoController
{
    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    public function echo(EchoRequest $request): ResponseInterface
    {
        return $this->apiResponseFactory->success([
            'received' => $request->validated(),
        ]);
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class HealthController
{
    public function __construct(
        private readonly ResponseFactoryInterface $response,
    ) {}

    public function __invoke(): ResponseInterface
    {
        return $this->response->jsonSuccess(['status' => 'ok']);
    }
}

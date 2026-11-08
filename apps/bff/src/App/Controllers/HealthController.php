<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class HealthController
{
    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
    ) {}

    public function check(): ResponseInterface
    {
        return $this->apiResponseFactory->success(['status' => 'ok']);
    }
}

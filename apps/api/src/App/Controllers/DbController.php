<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\Extensions\DatabaseEloquent\Contracts\DatabaseInterface;
use Psr\Http\Message\ResponseInterface;

final class DbController
{
    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
        private readonly DatabaseInterface $database,
    ) {}

    public function status(): ResponseInterface
    {
        $connection = $this->database->capsule()->getConnection();
        $connection->select('select 1 as ok');

        return $this->apiResponseFactory->success([
            'connection' => 'ok',
            'driver' => $connection->getDriverName(),
            'database' => $connection->getDatabaseName(),
        ]);
    }
}

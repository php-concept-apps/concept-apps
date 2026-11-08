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

        $schema = $connection->getSchemaBuilder();

        $catalog = [
            'categories' => $schema->hasTable('categories') ? $connection->table('categories')->count() : null,
            'products' => $schema->hasTable('products') ? $connection->table('products')->count() : null,
            'tags' => $schema->hasTable('tags') ? $connection->table('tags')->count() : null,
            'product_tag' => $schema->hasTable('product_tag') ? $connection->table('product_tag')->count() : null,
        ];

        return $this->apiResponseFactory->success([
            'connection' => 'ok',
            'driver' => $connection->getDriverName(),
            'database' => $connection->getDatabaseName(),
            'catalog' => $catalog,
        ]);
    }
}

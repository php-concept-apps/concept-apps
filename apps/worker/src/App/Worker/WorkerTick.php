<?php declare(strict_types=1);

namespace Concept\App\Worker;

use Concept\Extensions\DatabaseEloquent\Contracts\DatabaseInterface;
use Concept\Extensions\LoggerMonolog\Contracts\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * One worker iteration: log + DB connectivity check.
 */
final class WorkerTick
{
    private const string MSG_TICK = 'Worker tick';
    private const string ERR_VERSION_QUERY = 'VERSION() query failed';
    private const string ERR_TICK_FAILED = 'Worker tick failed: %s';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DatabaseInterface $database,
    ) {}

    public function run(): int
    {
        try {
            $pdo = $this->database->capsule()->getConnection()->getPdo();
            $statement = $pdo->query('SELECT VERSION()');
            if ($statement === false) {
                throw new RuntimeException(self::ERR_VERSION_QUERY);
            }
            $version = (string) $statement->fetchColumn();

            $this->logger->info(self::MSG_TICK, [
                'database' => $version,
            ]);
        } catch (Throwable $e) {
            $this->logger->error(sprintf(self::ERR_TICK_FAILED, $e->getMessage()));

            return 1;
        }

        return 0;
    }
}

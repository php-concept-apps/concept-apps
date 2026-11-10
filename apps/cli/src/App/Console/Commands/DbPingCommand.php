<?php declare(strict_types=1);

namespace Concept\App\Console\Commands;

use Concept\Extensions\DatabaseEloquent\Contracts\DatabaseInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class DbPingCommand extends Command
{
    private const string COMMAND_NAME = 'db:ping';
    private const string COMMAND_DESCRIPTION = 'Check database connectivity';
    private const string ERR_PING_FAILED = 'Database ping failed: %s';
    private const string ERR_VERSION_QUERY = 'VERSION() query failed';

    public function __construct(
        private readonly DatabaseInterface $database,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $pdo = $this->database->capsule()->getConnection()->getPdo();
            $statement = $pdo->query('SELECT VERSION()');
            if ($statement === false) {
                throw new RuntimeException(self::ERR_VERSION_QUERY);
            }
            $version = (string) $statement->fetchColumn();
        } catch (Throwable $e) {
            $io->error(sprintf(self::ERR_PING_FAILED, $e->getMessage()));

            return Command::FAILURE;
        }

        $io->success(sprintf('Database OK — %s', $version));

        return Command::SUCCESS;
    }
}

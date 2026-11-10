<?php declare(strict_types=1);

namespace Concept\App\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HelloCommand extends Command
{
    private const string COMMAND_NAME = 'app:hello';
    private const string COMMAND_DESCRIPTION = 'Smoke command for the CLI assembly';
    private const string OPT_NAME = 'name';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addOption(self::OPT_NAME, null, InputOption::VALUE_REQUIRED, 'Name to greet', 'CLI');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getOption(self::OPT_NAME);
        $label = is_string($name) && $name !== '' ? $name : 'CLI';

        $io->success(sprintf('Hello, %s — Concept CLI is up.', $label));

        return Command::SUCCESS;
    }
}

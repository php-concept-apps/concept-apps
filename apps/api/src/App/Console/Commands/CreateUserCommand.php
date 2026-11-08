<?php declare(strict_types=1);

namespace Concept\App\Console\Commands;

use Concept\App\Models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateUserCommand extends Command
{
    private const string COMMAND_NAME = 'auth:user';
    private const string COMMAND_DESCRIPTION = 'Create an API user (email + password)';
    private const string ARG_EMAIL = 'email';
    private const string ARG_PASSWORD = 'password';
    private const string ARG_NAME = 'name';

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addArgument(self::ARG_EMAIL, InputArgument::REQUIRED, 'User email')
            ->addArgument(self::ARG_PASSWORD, InputArgument::REQUIRED, 'Plain password')
            ->addArgument(self::ARG_NAME, InputArgument::OPTIONAL, 'Display name', 'API User');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument(self::ARG_EMAIL);
        $password = $input->getArgument(self::ARG_PASSWORD);
        $name = $input->getArgument(self::ARG_NAME);

        if (!is_string($email) || !is_string($password) || $email === '' || $password === '') {
            $io->error('email and password are required');

            return Command::FAILURE;
        }

        if (!is_string($name) || $name === '') {
            $name = 'API User';
        }

        if (User::query()->where('email', $email)->exists()) {
            $io->error(sprintf('User already exists: %s', $email));

            return Command::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $userId = $user->getKey();
        $io->success(sprintf(
            'Created user #%s <%s>',
            is_scalar($userId) ? (string) $userId : '?',
            $email,
        ));

        return Command::SUCCESS;
    }
}

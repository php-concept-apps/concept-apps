<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Foundation\AppConfig;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Foundation\PathName;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\PathManager\PathManager;
use Concept\Stack\Bricks\Console\ConsoleBrick;
use Concept\Stack\Bricks\Database\DatabaseBrick;
use Concept\Stack\Bricks\Logging\LoggingBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;
use Symfony\Component\Console\Command\Command;

/**
 * CLI stack: logging + database + console. No HTTP / session / view.
 */
final class ApplicationStackBootstrap
{
    public function boot(Container $container): void
    {
        $pathManager = ContainerDependency::get($container, PathManager::class);
        $config = ContainerDependency::get($container, ConfigInterface::class);
        $appConfig = new AppConfig($config);

        $stack = ConceptStack::create();

        $logFileName = $config->getString(ConfigKey::LOG_FILE, 'cli.log');
        $logMaxFiles = $config->getInt(ConfigKey::LOG_MAX_FILES, 14);
        $stack->add(new LoggingBrick())->builder()
            ->setLevel($config->getString(ConfigKey::LOG_LEVEL, 'INFO'))
            ->setChannel($config->getString(ConfigKey::LOG_NAME, 'cli'))
            ->toRotatingFile($pathManager->get(PathName::LOGS, $logFileName), $logMaxFiles);

        $database = $stack->add(new DatabaseBrick())->builder()
            ->setConnection($appConfig->dbConnection())
            ->setMigrations($pathManager->rootList($config->getStringList(ConfigKey::MIGRATIONS_PATHS)))
            ->setMigrationsTable($config->getString(ConfigKey::MIGRATIONS_TABLE, 'migrations'));

        if ($config->getBool(ConfigKey::DB_LOG_ENABLED)) {
            $database->withQueryLogging(
                $pathManager->get(PathName::LOGS, $config->getString(ConfigKey::DB_LOG_FILE, 'query.log')),
                $config->getInt(ConfigKey::DB_LOG_MAX_FILES, 7),
            );
        }

        $stack->add(new ConsoleBrick())->builder()
            ->setName($config->getString(ConfigKey::APP_NAME, 'Concept CLI'))
            ->setVersion($config->getString(ConfigKey::APP_VERSION, '0.1.0'))
            ->setCommands($config->getClassList(ConfigKey::COMMANDS, Command::class));

        foreach ($stack->providers() as $provider) {
            $container->addServiceProvider($provider);
        }
    }
}

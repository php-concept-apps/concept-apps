<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Foundation\AppConfig;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Foundation\PathName;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\DataMasker\Contracts\DataMaskerRuleInterface;
use Concept\Extensions\PathManager\PathManager;
use Concept\Stack\Bricks\Database\DatabaseBrick;
use Concept\Stack\Bricks\Logging\LoggingBrick;
use Concept\Stack\Bricks\Masking\MaskingBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;

/**
 * Worker stack: masking + logging + database. No HTTP / console / view.
 */
final class ApplicationStackBootstrap
{
    public function boot(Container $container): void
    {
        $pathManager = ContainerDependency::get($container, PathManager::class);
        $config = ContainerDependency::get($container, ConfigInterface::class);
        $appConfig = new AppConfig($config);

        $stack = ConceptStack::create();

        $stack->add(new MaskingBrick())->builder()
            ->setPatterns($config->getStringMap(ConfigKey::MASKING_PATTERNS))
            ->setKeyPatterns($config->getStringList(ConfigKey::MASKING_KEY_PATTERNS))
            ->setRules($config->getClassList(ConfigKey::MASKING_RULES, DataMaskerRuleInterface::class));

        $logFileName = $config->getString(ConfigKey::LOG_FILE, 'worker.log');
        $logMaxFiles = $config->getInt(ConfigKey::LOG_MAX_FILES, 14);
        $stack->add(new LoggingBrick())->builder()
            ->setLevel($config->getString(ConfigKey::LOG_LEVEL, 'INFO'))
            ->setChannel($config->getString(ConfigKey::LOG_NAME, 'worker'))
            ->toRotatingFile($pathManager->get(PathName::LOGS, $logFileName), $logMaxFiles)
            ->withMasking();

        $stack->add(new DatabaseBrick())->builder()
            ->setConnection($appConfig->dbConnection())
            ->withMasking();

        foreach ($stack->providers() as $provider) {
            $container->addServiceProvider($provider);
        }
    }
}

<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Foundation\AppConfig;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Foundation\PathName;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\DataMasker\Contracts\DataMaskerRuleInterface;
use Concept\Extensions\PathManager\PathManager;
use Concept\Extensions\ValidationRakit\Contracts\RuleInterface;
use Concept\Stack\Bricks\Casting\CastingBrick;
use Concept\Stack\Bricks\Console\ConsoleBrick;
use Concept\Stack\Bricks\Database\DatabaseBrick;
use Concept\Stack\Bricks\ErrorHandling\ErrorHandlingBrick;
use Concept\Stack\Bricks\Http\HttpBrick;
use Concept\Stack\Bricks\Logging\LoggingBrick;
use Concept\Stack\Bricks\Masking\MaskingBrick;
use Concept\Stack\Bricks\Session\SessionBrick;
use Concept\Stack\Bricks\Validation\ValidationBrick;
use Concept\Stack\Bricks\View\ViewBrick;
use Concept\Stack\Bricks\ViewTwig\ViewTwigBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;
use Symfony\Component\Console\Command\Command;
use Whoops\Handler\PrettyPageHandler;

/**
 * Web stack: session + CSRF + Twig + database (+ console for migrate/seed).
 */
final class ApplicationStackBootstrap
{
    public function boot(Container $container): void
    {
        $pathManager = ContainerDependency::get($container, PathManager::class);
        $config = ContainerDependency::get($container, ConfigInterface::class);
        $app = new AppConfig($config);
        $debug = $config->getBool(ConfigKey::APP_DEBUG);

        $stack = ConceptStack::create();

        $stack->add(new MaskingBrick())->builder()
            ->setPatterns($config->getStringMap(ConfigKey::MASKING_PATTERNS))
            ->setKeyPatterns($config->getStringList(ConfigKey::MASKING_KEY_PATTERNS))
            ->setRules($config->getClassList(ConfigKey::MASKING_RULES, DataMaskerRuleInterface::class));

        $logFileName = $config->getString(ConfigKey::LOG_FILE);
        $logMaxFiles = $config->getInt(ConfigKey::LOG_MAX_FILES);
        $stack->add(new LoggingBrick())->builder()
            ->setLevel($config->getString(ConfigKey::LOG_LEVEL))
            ->setChannel($config->getString(ConfigKey::LOG_NAME))
            ->toRotatingFile($pathManager->get(PathName::LOGS, $logFileName), $logMaxFiles)
            ->withMasking();

        $casterCacheDir = $config->getString(ConfigKey::CASTER_CACHE_DIR);
        $stack->add(new CastingBrick())->builder()
            ->setCacheDir($pathManager->get(PathName::CACHE, $casterCacheDir))
            ->setDebug($debug);

        $validation = $stack->add(new ValidationBrick())->builder()
            ->setRules($config->getClassMap(ConfigKey::VALIDATOR_RULES, RuleInterface::class))
            ->setGlobalExcept($config->getStringList(ConfigKey::FORM_REQUEST_GLOBAL_EXCEPT));
        if ($config->getBool(ConfigKey::VALIDATOR_LOG_ENABLED)) {
            $validationLogFile = $config->getString(ConfigKey::VALIDATOR_LOG_FILE);
            $validation->withLogging($pathManager->get(PathName::LOGS, $validationLogFile));
        }
        $validation->withMasking();

        $database = $stack->add(new DatabaseBrick())->builder()
            ->setConnection($app->dbConnection())
            ->setMigrations($pathManager->rootList($config->getStringList(ConfigKey::MIGRATIONS_PATHS)))
            ->setMigrationsTable($config->getString(ConfigKey::MIGRATIONS_TABLE))
            ->setSeeders($config->getClassStringList(ConfigKey::SEEDERS_LIST))
            ->withMasking();

        if ($config->getBool(ConfigKey::DB_LOG_ENABLED)) {
            $database->withQueryLogging(
                $pathManager->get(PathName::LOGS, $config->getString(ConfigKey::DB_LOG_FILE)),
                $config->getInt(ConfigKey::DB_LOG_MAX_FILES),
            );
        }

        $stack->add(new SessionBrick())->builder()
            ->setOptions($app->sessionOptions())
            ->setHandlerFactory($app->getSessionHandlerFactory($pathManager))
            ->withCsrf();

        $routeFiles = $config->getStringList(ConfigKey::ROUTES_LIST);
        $stack->add(new HttpBrick())->builder()
            ->setRoutes($pathManager->rootList($routeFiles))
            ->withFormRequests()
            ->withTypedRouteParameters();

        $viewCacheDir = $config->getString(ConfigKey::VIEW_CACHE_DIR);
        $twig = new ViewTwigBrick();
        $twig->builder()
            ->setViewsPath($pathManager->get(PathName::VIEWS))
            ->setCacheDir($pathManager->get(PathName::CACHE, $viewCacheDir))
            ->setDebug($debug);

        $viewExtensions = $config->getClassStringList(ConfigKey::VIEW_EXTENSIONS);
        $stack->add(new ViewBrick())->builder()
            ->setPaths($pathManager->rootMap($config->getStringMap(ConfigKey::VIEW_PATHS)))
            ->setExtensions($viewExtensions)
            ->setRouteNamespaces($config->getStringMap(ConfigKey::VIEW_ROUTE_NAMESPACE))
            ->withEngine($twig);

        $stack->add(new ConsoleBrick())->builder()
            ->setName($config->getString(ConfigKey::APP_NAME))
            ->setVersion($config->getString(ConfigKey::APP_VERSION))
            ->setCommands($config->getClassList(ConfigKey::COMMANDS, Command::class))
            ->withLogging();

        $stack->add(new ErrorHandlingBrick())->builder()
            ->setDebug($debug, new PrettyPageHandler())
            ->setLogReporting()
            ->withViewRenderer($pathManager->get(PathName::ERRORS_FALLBACK_VIEWS));

        foreach ($stack->providers() as $provider) {
            $container->addServiceProvider($provider);
        }
    }
}

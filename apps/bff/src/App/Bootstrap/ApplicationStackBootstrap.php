<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Foundation\ConfigKey;
use Concept\App\Foundation\PathName;
use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Http\Error\ApiHttpErrorRenderer;
use Concept\App\Http\Response\ApiResponseFactory;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\DataMasker\Contracts\DataMaskerRuleInterface;
use Concept\Extensions\ErrorHandlerWhoops\Contracts\HttpErrorRendererInterface;
use Concept\Extensions\Http\Contracts\ResponseFactoryInterface;
use Concept\Extensions\PathManager\PathManager;
use Concept\Extensions\ValidationRakit\Contracts\RuleInterface;
use Concept\Stack\Bricks\Casting\CastingBrick;
use Concept\Stack\Bricks\ErrorHandling\ErrorHandlingBrick;
use Concept\Stack\Bricks\Http\HttpBrick;
use Concept\Stack\Bricks\Logging\LoggingBrick;
use Concept\Stack\Bricks\Masking\MaskingBrick;
use Concept\Stack\Bricks\Validation\ValidationBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;
use League\Container\DefinitionContainerInterface;

/**
 * BFF stack: HTTP + validation + masking. No DB / session / view.
 */
final class ApplicationStackBootstrap
{
    public function boot(Container $container): void
    {
        $pathManager = ContainerDependency::get($container, PathManager::class);
        $config = ContainerDependency::get($container, ConfigInterface::class);
        $debug = $config->getBool(ConfigKey::APP_DEBUG);

        $stack = ConceptStack::create();

        $stack->add(new MaskingBrick())->builder()
            ->setPatterns($config->getStringMap(ConfigKey::MASKING_PATTERNS))
            ->setKeyPatterns($config->getStringList(ConfigKey::MASKING_KEY_PATTERNS))
            ->setRules($config->getClassList(ConfigKey::MASKING_RULES, DataMaskerRuleInterface::class));

        $logFileName = $config->getString(ConfigKey::LOG_FILE, 'bff.log');
        $logMaxFiles = $config->getInt(ConfigKey::LOG_MAX_FILES, 14);
        $stack->add(new LoggingBrick())->builder()
            ->setLevel($config->getString(ConfigKey::LOG_LEVEL, 'INFO'))
            ->setChannel($config->getString(ConfigKey::LOG_NAME, 'bff'))
            ->toRotatingFile($pathManager->get(PathName::LOGS, $logFileName), $logMaxFiles)
            ->withMasking();

        $casterCacheDir = $config->getString(ConfigKey::CASTER_CACHE_DIR, 'valinor');
        $stack->add(new CastingBrick())->builder()
            ->setCacheDir($pathManager->get(PathName::CACHE, $casterCacheDir))
            ->setDebug($debug);

        $validation = $stack->add(new ValidationBrick())->builder()
            ->setRules($config->getClassMap(ConfigKey::VALIDATOR_RULES, RuleInterface::class))
            ->setGlobalExcept($config->getStringList(ConfigKey::FORM_REQUEST_GLOBAL_EXCEPT));
        if ($config->getBool(ConfigKey::VALIDATOR_LOG_ENABLED)) {
            $validationLogFile = $config->getString(ConfigKey::VALIDATOR_LOG_FILE, 'validation.log');
            $validation->withLogging($pathManager->get(PathName::LOGS, $validationLogFile));
        }
        $validation->withMasking();

        $routeFiles = $config->getStringList(ConfigKey::ROUTES_LIST);
        $stack->add(new HttpBrick())->builder()
            ->setRoutes($pathManager->rootList($routeFiles))
            ->withFormRequests()
            ->withTypedRouteParameters();

        $stack->add(new ErrorHandlingBrick())->builder()
            ->setDebug($debug)
            ->setLogReporting()
            ->setRenderer(static function(DefinitionContainerInterface $container): HttpErrorRendererInterface {
                return new ApiHttpErrorRenderer(
                    ContainerDependency::get($container, ApiResponseFactoryInterface::class),
                );
            });

        foreach ($stack->providers() as $provider) {
            $container->addServiceProvider($provider);
        }

        $container->add(
            ApiResponseFactoryInterface::class,
            static fn(): ApiResponseFactory => new ApiResponseFactory(
                ContainerDependency::get($container, ResponseFactoryInterface::class),
            ),
        )->setShared(true);
    }
}

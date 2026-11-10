<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\Extensions\ErrorHandlerWhoops\EarlyWhoopsServiceProvider;
use Concept\Extensions\ErrorHandlerWhoops\Handlers\ReportExceptionHandler;
use Concept\Stack\Bricks\ErrorHandling\Reporting\PhpErrorLogReporter;
use League\Container\Container;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run as Whoops;

final class EarlyErrorHandlingBootstrap
{
    public function __construct(
        private readonly bool $debug,
    ) {}

    public function boot(Container $container): void
    {
        $handler = new PlainTextHandler();
        if ($this->debug) {
            $handler->addTraceToOutput(true);
        }

        $container->add(Whoops::class, EarlyWhoopsServiceProvider::register(
            $handler,
            new ReportExceptionHandler(static fn() => new PhpErrorLogReporter()),
        ))->setShared(true);
    }
}

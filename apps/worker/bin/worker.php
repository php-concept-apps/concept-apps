<?php declare(strict_types=1);

use Concept\App\Worker\WorkerTick;
use Concept\Core\App;

require __DIR__ . '/../vendor/autoload.php';

/** @var App $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var WorkerTick $tick */
$tick = $app->getContainer()->get(WorkerTick::class);

/** @var list<string> $argv */
$argv = $_SERVER['argv'] ?? [];
$loop = in_array('--loop', $argv, true);
$interval = 5;

do {
    $code = $tick->run();
    if ($code !== 0 || !$loop) {
        exit($code);
    }
    sleep($interval);
} while (true);

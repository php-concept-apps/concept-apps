<?php declare(strict_types=1);

use Concept\App\Console\Commands\DbPingCommand;
use Concept\App\Console\Commands\HelloCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrateCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrationListCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrationPathsCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbRollbackCommand;

return [
    'console' => [
        'commands' => [
            HelloCommand::class,
            DbPingCommand::class,
            DbMigrateCommand::class,
            DbMigrationListCommand::class,
            DbMigrationPathsCommand::class,
            DbRollbackCommand::class,
        ],
    ],
];

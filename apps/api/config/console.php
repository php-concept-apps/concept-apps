<?php declare(strict_types=1);

use Concept\App\Console\Commands\FillCatalogCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrateCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrationListCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbMigrationPathsCommand;
use Concept\Extensions\DatabaseEloquent\Console\Commands\DbRollbackCommand;
use Concept\Extensions\Http\Console\Commands\RouteListCommand;

return [
    'console' => [
        'commands' => [
            DbMigrateCommand::class,
            DbMigrationListCommand::class,
            DbMigrationPathsCommand::class,
            DbRollbackCommand::class,
            RouteListCommand::class,
            FillCatalogCommand::class,
        ],
    ],
];

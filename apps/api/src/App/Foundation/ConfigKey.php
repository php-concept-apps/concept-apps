<?php declare(strict_types=1);

namespace Concept\App\Foundation;

/** Config keys used by the API assembly. */
final class ConfigKey
{
    public const string APP_DEBUG = 'app.debug';
    public const string APP_NAME = 'app.name';
    public const string APP_VERSION = 'app.version';
    public const string APP_TIMEZONE = 'app.timezone';

    public const string ROUTES_LIST = 'routes.list';
    public const string ROUTES_RESOLVERS = 'routes.resolvers';

    public const string COMPONENTS = 'components.list';

    public const string COMMANDS = 'console.commands';

    public const string LOG_FILE = 'log.file';
    public const string LOG_NAME = 'log.name';
    public const string LOG_LEVEL = 'log.level';
    public const string LOG_MAX_FILES = 'log.max_files';

    public const string VALIDATOR_RULES = 'validator.rules';
    public const string VALIDATOR_LOG_ENABLED = 'validator.log_enabled';
    public const string VALIDATOR_LOG_FILE = 'validator.log_file';

    public const string FORM_REQUEST_GLOBAL_EXCEPT = 'form_request.global_except';

    public const string CASTER_CACHE_DIR = 'caster.cache_dir';

    public const string MASKING_PATTERNS = 'masking.patterns';
    public const string MASKING_KEY_PATTERNS = 'masking.key_patterns';
    public const string MASKING_RULES = 'masking.rules';

    public const string DB_DRIVER = 'db.driver';
    public const string DB_HOST = 'db.host';
    public const string DB_PORT = 'db.port';
    public const string DB_DATABASE = 'db.database';
    public const string DB_USERNAME = 'db.username';
    public const string DB_PASSWORD = 'db.password';
    public const string DB_CHARSET = 'db.charset';
    public const string DB_COLLATION = 'db.collation';
    public const string DB_PREFIX = 'db.prefix';

    public const string DB_LOG_ENABLED = 'db.log_enabled';
    public const string DB_LOG_FILE = 'db.log_file';
    public const string DB_LOG_MAX_FILES = 'db.log_max_files';

    public const string MIGRATIONS_TABLE = 'migrations.table';
    public const string MIGRATIONS_PATHS = 'migrations.paths';

    public const string PAGINATION_PER_PAGE = 'pagination.per_page';
    public const string PAGINATION_MAX_PER_PAGE = 'pagination.max_per_page';
}

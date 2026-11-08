<?php declare(strict_types=1);

namespace Concept\App\Foundation;

/** Config keys used by the BFF assembly. */
final class ConfigKey
{
    public const string APP_DEBUG = 'app.debug';
    public const string APP_NAME = 'app.name';
    public const string APP_TIMEZONE = 'app.timezone';

    public const string ROUTES_LIST = 'routes.list';

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
}

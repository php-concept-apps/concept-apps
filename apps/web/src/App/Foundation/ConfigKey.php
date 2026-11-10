<?php declare(strict_types=1);

namespace Concept\App\Foundation;

/** Config keys used by the web assembly. */
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

    public const string SESSION_COOKIE_LIFETIME = 'session.cookie.lifetime';
    public const string SESSION_COOKIE_PATH = 'session.cookie.path';
    public const string SESSION_COOKIE_SECURE = 'session.cookie.secure';
    public const string SESSION_COOKIE_HTTPONLY = 'session.cookie.httponly';
    public const string SESSION_COOKIE_DOMAIN = 'session.cookie.domain';
    public const string SESSION_COOKIE_SAMESITE = 'session.cookie.samesite';
    public const string SESSION_OPTIONS_USE_ONLY_COOKIES = 'session.options.use_only_cookies';
    public const string SESSION_OPTIONS_USE_STRICT_MODE = 'session.options.use_strict_mode';
    public const string SESSION_FILE_PATH = 'session.file.path';

    public const string VIEW_PATHS = 'view.paths';
    public const string VIEW_CACHE_DIR = 'view.cache_dir';
    public const string VIEW_EXTENSIONS = 'view.extensions';
    public const string VIEW_ROUTE_NAMESPACE = 'view.route_namespace';
}

<?php declare(strict_types=1);

namespace Concept\App\Foundation;

use Closure;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\PathManager\PathManager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

final class AppConfig
{
    public function __construct(
        private readonly ConfigInterface $config,
    ) {}

    /**
     * @return array{
     *     cookie_lifetime: int,
     *     cookie_path: string,
     *     cookie_secure: bool,
     *     cookie_httponly: bool,
     *     cookie_domain: string,
     *     cookie_samesite: string,
     *     use_only_cookies: bool,
     *     use_strict_mode: bool
     * }
     */
    public function sessionOptions(): array
    {
        return [
            'cookie_lifetime' => $this->config->getInt(ConfigKey::SESSION_COOKIE_LIFETIME),
            'cookie_path' => $this->config->getString(ConfigKey::SESSION_COOKIE_PATH, '/'),
            'cookie_secure' => $this->config->getBool(ConfigKey::SESSION_COOKIE_SECURE),
            'cookie_httponly' => $this->config->getBool(ConfigKey::SESSION_COOKIE_HTTPONLY, true),
            'cookie_domain' => $this->config->getString(ConfigKey::SESSION_COOKIE_DOMAIN),
            'cookie_samesite' => $this->config->getString(ConfigKey::SESSION_COOKIE_SAMESITE, 'Lax'),
            'use_only_cookies' => $this->config->getBool(ConfigKey::SESSION_OPTIONS_USE_ONLY_COOKIES, true),
            'use_strict_mode' => $this->config->getBool(ConfigKey::SESSION_OPTIONS_USE_STRICT_MODE, true),
        ];
    }

    /**
     * @return Closure(): NativeFileSessionHandler
     */
    public function getSessionHandlerFactory(PathManager $pathManager): Closure
    {
        $sessionFilePath = $this->config->get(ConfigKey::SESSION_FILE_PATH);

        return static function() use ($pathManager, $sessionFilePath): NativeFileSessionHandler {
            return is_string($sessionFilePath) && $sessionFilePath !== ''
                ? new NativeFileSessionHandler($pathManager->root($sessionFilePath))
                : new NativeFileSessionHandler();
        };
    }
}

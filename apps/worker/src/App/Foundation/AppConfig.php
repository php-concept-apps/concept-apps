<?php declare(strict_types=1);

namespace Concept\App\Foundation;

use Concept\Extensions\Config\Contracts\ConfigInterface;

/** App glue helpers for composite config shapes. */
final class AppConfig
{
    public function __construct(
        private readonly ConfigInterface $config,
    ) {}

    /**
     * @return array{
     *     driver: string,
     *     host: string,
     *     port: int,
     *     database: string,
     *     username: string,
     *     password: string,
     *     charset: string,
     *     collation: string,
     *     prefix: string
     * }
     */
    public function dbConnection(): array
    {
        return [
            'driver' => $this->config->getString(ConfigKey::DB_DRIVER),
            'host' => $this->config->getString(ConfigKey::DB_HOST),
            'port' => $this->config->getInt(ConfigKey::DB_PORT, 3306),
            'database' => $this->config->getString(ConfigKey::DB_DATABASE),
            'username' => $this->config->getString(ConfigKey::DB_USERNAME),
            'password' => $this->config->getString(ConfigKey::DB_PASSWORD),
            'charset' => $this->config->getString(ConfigKey::DB_CHARSET, 'utf8mb4'),
            'collation' => $this->config->getString(ConfigKey::DB_COLLATION, 'utf8mb4_unicode_ci'),
            'prefix' => $this->config->getString(ConfigKey::DB_PREFIX),
        ];
    }
}

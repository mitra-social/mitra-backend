<?php

declare(strict_types=1);

namespace Mitra\Config;

use Chubbyphp\Config\ConfigInterface;
use Doctrine\Common\Proxy\AbstractProxyFactory;

final class DevConfig implements ConfigInterface
{

    /**
     * @var string
     */
    protected const ENV_DB_HOST = 'DB_HOST';

    /**
     * @var string
     */
    protected const ENV_DB_USER = 'DB_USER';

    /**
     * @var string
     */
    protected const ENV_DB_PW = 'DB_PW';

    /**
     * @var string
     */
    public const ENV_APP_ENV = 'APP_ENV';

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'env' => getenv(self::ENV_APP_ENV),
            'debug' => true,
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
            'doctrine.dbal.db.options' => [
                'connection' => [
                    'driver' => 'pdo_mysql',
                    'host' => getenv(self::ENV_DB_HOST),
                    'dbname' => 'mitra',
                    'user' => getenv(self::ENV_DB_USER),
                    'password' => getenv(self::ENV_DB_PW),
                    'charset' => 'utf8mb4',
                ],
            ],
            'doctrine.orm.em.options' => [
                'proxies.auto_generate' => AbstractProxyFactory::AUTOGENERATE_EVAL,
            ],
            'doctrine.migrations.directory' => $this->rootDir . '/migrations/',
            'doctrine.migrations.namespace' => 'Jobcloud\Marketplace\Core\Migrations',
            'doctrine.migrations.table' => 'doctrine_migration_version',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDirectories(): array
    {
        $environment = $this->getEnv();

        return [
            'cache' => $this->rootDir . '/var/cache/' . $environment,
            'logs' => $this->rootDir . '/var/logs/' . $environment,
        ];
    }

    public function getEnv(): string
    {
        if (false === ($env = getenv(self::ENV_APP_ENV))) {
            throw new \InvalidArgumentException(sprintf('Environment variable `%s` is not set.', self::ENV_APP_ENV));
        }

        return $env;
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Config;

use Chubbyphp\Config\ConfigInterface;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use Mitra\Entity\User;
use Mitra\Mapping\Orm\UserOrmMapping;
use Mitra\Mapping\Validation\NestedDtoValidationMapping;
use Mitra\Mapping\Validation\UserDtoValidationMapping;
use ProxyManager\Factory\AbstractBaseFactory;

final class Config implements ConfigInterface
{

    /**
     * @var string
     */
    private const ENV_DB_HOST = 'DB_HOST';

    /**
     * @var string
     */
    private const ENV_DB_USER = 'DB_USER';

    /**
     * @var string
     */
    private const ENV_DB_PW = 'DB_PW';

    /**
     * string
     */
    private const ENV_DB_NAME = 'DB_NAME';

    /**
     * @var string
     */
    private const ENV_APP_ENV = 'APP_ENV';

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
        $env = $this->getEnv();

        $config = [
            'env' => $env,
            'debug' => false,
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
            'doctrine.dbal.db.options' => [
                'connection' => [
                    'driver' => 'pdo_mysql',
                    'host' => getenv(self::ENV_DB_HOST),
                    'dbname' => getenv(self::ENV_DB_NAME),
                    'user' => getenv(self::ENV_DB_USER),
                    'password' => getenv(self::ENV_DB_PW),
                    'charset' => 'utf8mb4',
                ],
            ],
            'doctrine.orm.em.options' => [
                'proxies.auto_generate' => false,
            ],
            'doctrine.migrations.directory' => $this->rootDir . '/migrations/',
            'doctrine.migrations.namespace' => 'Mitra\Core\Migrations',
            'doctrine.migrations.table' => 'doctrine_migration_version',
            'mappings' => [
                'orm' => [
                    User::class => UserOrmMapping::class,
                ],
                'validation' => [
                    UserDto::class => new UserDtoValidationMapping(),
                    NestedDto::class => new NestedDtoValidationMapping(),
                ],
                'command_handlers' => [
                    CreateUserCommand::class => CreateUserCommandHandler::class
                ],
            ]
        ];

        if ('dev' === $env) {
            $config['debug'] = true;
            $config['doctrine.orm.em.options']['proxies.auto_generate'] = true;
        }

        return $config;
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

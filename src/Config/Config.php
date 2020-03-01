<?php

declare(strict_types=1);

namespace Mitra\Config;

use Chubbyphp\Config\ConfigInterface;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use Mitra\Entity\User;
use Mitra\Env\Env;
use Mitra\Mapping\Orm\UserOrmMapping;
use Mitra\Mapping\Validation\NestedDtoValidationMapping;
use Mitra\Mapping\Validation\UserDtoValidationMapping;
use Monolog\Logger;

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
     * string
     */
    private const ENV_DB_PORT = 'DB_PORT';


    /**
     * string
     */
    private const ENV_DATABASE_URL = 'DATABASE_URL';

    /**
     * @var string
     */
    private const ENV_APP_ENV = 'APP_ENV';

    /**
     * @var string
     */
    private const ENV_APP_DEBUG = 'APP_DEBUG';

    /**
     * @var string
     */
    private const ENV_JWT_SECRET = 'JWT_SECRET';

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Env
     */
    private $env;

    /**
     * @param string $rootDir
     * @param Env $env
     */
    public function __construct(string $rootDir, Env $env)
    {
        $this->rootDir = $rootDir;
        $this->env = $env;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        $appEnv = $this->getEnv();
        $dirs = $this->getDirectories();

        if (null !== $databaseUrl = $this->env->get(self::ENV_DATABASE_URL)) {
            $dbConf = parse_url($databaseUrl);
        } else {
            $dbConf = [
                'host' => 'localhost',
                'dbname' => 'mitra',
                'port' => 5432,
                'user' => 'root',
                'password' => '',
            ];
        }

        $config = [
            'env' => $appEnv,
            'debug' => (bool) $this->env->get(self::ENV_APP_DEBUG),
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
            'doctrine.dbal.db.options' => [
                'connection' => [
                    'driver' => 'pdo_pgsql',
                    'host' => $this->env->get(self::ENV_DB_HOST) ?? $dbConf['host'],
                    'dbname' => $this->env->get(self::ENV_DB_NAME) ?? ltrim($dbConf['path'], '/'),
                    'port' => $this->env->get(self::ENV_DB_PORT) ?? $dbConf['port'],
                    'user' => $this->env->get(self::ENV_DB_USER) ?? $dbConf['user'],
                    'password' => $this->env->get(self::ENV_DB_PW) ?? $dbConf['pass'],
                    'charset' => 'utf8',
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
            ],
            'monolog.name' => 'default',
            'monolog.path' => $dirs['logs'] . '/application.log',
            'monolog.level' => Logger::NOTICE,
            'jwt.secret' => $this->env->get(self::ENV_JWT_SECRET),
        ];

        if ('dev' === $appEnv) {
            $config['doctrine.orm.em.options']['proxies.auto_generate'] = true;
            $config['monolog.level'] = Logger::DEBUG;
        }

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getDirectories(): array
    {
        $appEnv = $this->getEnv();

        return [
            'cache' => $this->rootDir . '/var/cache/' . $appEnv,
            'logs' => $this->rootDir . '/var/logs/' . $appEnv,
        ];
    }

    public function getEnv(): string
    {
        return $this->env->get(self::ENV_APP_ENV);
    }
}

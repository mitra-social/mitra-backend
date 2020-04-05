<?php

declare(strict_types=1);

namespace Mitra\Config;

use ActivityPhp\Type\Extended\Activity\Follow;
use ActivityPhp\Type\Extended\Object\Image;
use Chubbyphp\Config\ConfigInterface;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\Handler\ActivityPub\FollowCommandHandler;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\Request\TokenRequestDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\ArticleDto;
use Mitra\Dto\Response\ActivityStreams\DocumentDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\AbstractUser;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Env\Env;
use Mitra\Mapping\Orm\ActivityStreamContentAssignmentOrmMapping;
use Mitra\Mapping\Orm\ActivityStreamContentOrmMapping;
use Mitra\Mapping\Orm\Actor\ActorOrmMapping;
use Mitra\Mapping\Orm\Actor\OrganizationOrmMapping;
use Mitra\Mapping\Orm\Actor\PersonOrmMapping;
use Mitra\Mapping\Orm\User\AbstractUserOrmMapping;
use Mitra\Mapping\Orm\User\ExternalUserOrmMapping;
use Mitra\Mapping\Orm\User\InternalUserOrmMapping;
use Mitra\Mapping\Validation\ActivityPub\ActivityDtoValidationMapping;
use Mitra\Mapping\Validation\ActivityPub\ObjectDtoValidationMapping;
use Mitra\Mapping\Validation\TokenRequestDtoValidationMapping;
use Mitra\Mapping\Validation\CreateUserRequestDtoValidationMapping;
use Monolog\Logger;

final class Config implements ConfigInterface
{

    /**
     * @var string
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
    private const ENV_BASE_URL = 'BASE_URL';

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

        $config = [
            'env' => $appEnv,
            'baseUrl' => $this->env->get(self::ENV_BASE_URL),
            'debug' => (bool) $this->env->get(self::ENV_APP_DEBUG),
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
            'doctrine.dbal.db.options' => [
                'connection' => [
                    'url' => $this->env->get(self::ENV_DATABASE_URL),
                    'charset' => 'utf8'
                ],
            ],
            'doctrine.orm.em.options' => [
                'proxies.auto_generate' => false,
            ],
            'doctrine.migrations.directory' => $this->rootDir . '/migrations/',
            'doctrine.migrations.namespace' => 'Mitra\Migrations',
            'doctrine.migrations.table' => 'doctrine_migration_version',
            'mappings' => [
                'orm' => [
                    AbstractUser::class => AbstractUserOrmMapping::class,
                    ExternalUser::class => ExternalUserOrmMapping::class,
                    InternalUser::class => InternalUserOrmMapping::class,
                    ActivityStreamContent::class => ActivityStreamContentOrmMapping::class,
                    ActivityStreamContentAssignment::class => ActivityStreamContentAssignmentOrmMapping::class,
                    Actor::class => ActorOrmMapping::class,
                    Person::class => PersonOrmMapping::class,
                    Organization::class => OrganizationOrmMapping::class,
                ],
                'validation' => [
                    CreateUserRequestDto::class => CreateUserRequestDtoValidationMapping::class,
                    TokenRequestDto::class => TokenRequestDtoValidationMapping::class,

                    // ActivityPub
                    ObjectDto::class => ObjectDtoValidationMapping::class,
                    // TODO: LinkDto::class => ,
                    ArticleDto::class => ObjectDtoValidationMapping::class,
                    DocumentDto::class => ObjectDtoValidationMapping::class,
                    Image::class => ObjectDtoValidationMapping::class,

                    PersonDto::class => ObjectDtoValidationMapping::class,

                    FollowDto::class => ActivityDtoValidationMapping::class,
                    CreateDto::class => ActivityDtoValidationMapping::class,
                ],
                'command_handlers' => [
                    CreateUserCommand::class => CreateUserCommandHandler::class,
                    FollowCommand::class => FollowCommandHandler::class,
                ],
            ],
            'monolog.name' => 'default',
            'monolog.handlers' => [
                'php://stderr' => Logger::INFO,
            ],
            'jwt.secret' => $this->env->get(self::ENV_JWT_SECRET),
        ];

        if ('dev' === $appEnv) {
            $config['doctrine.orm.em.options']['proxies.auto_generate'] = true;
            $config['monolog.handlers'] = [
                'php://stderr' => Logger::DEBUG,
                $dirs['logs'] . '/application.log' => Logger::DEBUG,
            ];
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

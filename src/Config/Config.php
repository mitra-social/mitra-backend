<?php

declare(strict_types=1);

namespace Mitra\Config;

use ActivityPhp\Type\Extended\Object\Image;
use Chubbyphp\Config\ConfigInterface;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Command\ActivityPub\AssignActorCommand;
use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\CommandBus\Command\ActivityPub\SendObjectToRecipientsCommand;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\CommandBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAttributedEvent;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;
use Mitra\CommandBus\Handler\Command\ActivityPub\AssignActivityStreamContentToFollowersCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\AssignActorCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\AttributeActivityStreamContentCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\FollowCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\PersistActivityStreamContentCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\SendObjectToRecipientsCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\UndoCommandHandler;
use Mitra\CommandBus\Handler\Command\ActivityPub\ValidateContentCommandHandler;
use Mitra\CommandBus\Handler\Command\CreateUserCommandHandler;
use Mitra\CommandBus\Handler\Event\ActivityPub\ActivityStreamContentAttributedEventHandler;
use Mitra\CommandBus\Handler\Event\ActivityPub\ActivityStreamContentPersistedEventHandler;
use Mitra\CommandBus\Handler\Event\ActivityPub\ActivityStreamContentReceivedEventHandler;
use Mitra\CommandBus\Handler\Event\ActivityPub\ContentAcceptedEventHandler;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\Request\TokenRequestDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\ArticleDto;
use Mitra\Dto\Response\ActivityStreams\DocumentDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\AbstractUser;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Env\Env;
use Mitra\Mapping\Orm\ActivityStreamContentAssignmentOrmMapping;
use Mitra\Mapping\Orm\ActivityStreamContentOrmMapping;
use Mitra\Mapping\Orm\Actor\ActorOrmMapping;
use Mitra\Mapping\Orm\Actor\OrganizationOrmMapping;
use Mitra\Mapping\Orm\Actor\PersonOrmMapping;
use Mitra\Mapping\Orm\SubscriptionOrmMapping;
use Mitra\Mapping\Orm\User\AbstractUserOrmMapping;
use Mitra\Mapping\Orm\User\ExternalUserOrmMapping;
use Mitra\Mapping\Orm\User\InternalUserOrmMapping;
use Mitra\Mapping\Validation\ActivityPub\ActivityDtoValidationMapping;
use Mitra\Mapping\Validation\ActivityPub\ObjectDtoValidationMapping;
use Mitra\Mapping\Validation\TokenRequestDtoValidationMapping;
use Mitra\Mapping\Validation\CreateUserRequestDtoValidationMapping;
use Monolog\Logger;
use Symfony\Component\Messenger\Transport\TransportInterface;

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
    private const ENV_QUEUE_DNS = 'QUEUE_DNS';

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

        $envVarValues = $this->getRequiredEnvVariables([
            self::ENV_APP_DEBUG,
            self::ENV_DATABASE_URL,
            self::ENV_BASE_URL,
        ]);

        $config = [
            'env' => $appEnv,
            'baseUrl' => $envVarValues[self::ENV_BASE_URL],
            'debug' => (bool) $envVarValues[self::ENV_APP_DEBUG],
            'rootDir' => $this->rootDir,
            'routerCacheFile' => null,
            'doctrine.dbal.db.options' => [
                'connection' => [
                    'url' => $envVarValues[self::ENV_DATABASE_URL],
                    'charset' => 'utf8'
                ],
            ],
            'doctrine.orm.em.options' => [
                'proxies.auto_generate' => false,
            ],
            'doctrine.migrations.directory' => $this->rootDir . '/migrations/',
            'doctrine.migrations.namespace' => 'Mitra\Migrations',
            'doctrine.migrations.table' => 'doctrine_migration_version',
            'queue_dns' => $this->env->get(self::ENV_QUEUE_DNS),
            'mappings' => [
                'orm' => $this->getMappingOrm(),
                'validation' => $this->getMappingValidation(),
                'bus' => $this->getMappingBus(),
            ],
            'command_bus.event_subscribers' => [
                ContentAcceptedEvent::class => [
                    ContentAcceptedEventHandler::class,
                ],
            ],
            'monolog.name' => 'default',
            'monolog.handlers' => [
                sprintf('%s/application.log', $dirs['logs']) => Logger::DEBUG,
            ],
            'jwt.secret' => $this->env->get(self::ENV_JWT_SECRET),
        ];

        if ('dev' === $appEnv) {
            $config['doctrine.orm.em.options']['proxies.auto_generate'] = true;
            $config['monolog.handlers'] = [
                sprintf('%s/application.log', $dirs['logs']) => Logger::DEBUG,
            ];
            // We don't want to send any message to a queue for development
            $config['mapping']['bus']['routing'] = [];
        }

        return $config;
    }

    /**
     * @return array<string, string>
     */
    private function getMappingOrm(): array
    {
        return [
            AbstractUser::class => AbstractUserOrmMapping::class,
            ExternalUser::class => ExternalUserOrmMapping::class,
            InternalUser::class => InternalUserOrmMapping::class,
            ActivityStreamContent::class => ActivityStreamContentOrmMapping::class,
            ActivityStreamContentAssignment::class => ActivityStreamContentAssignmentOrmMapping::class,
            Actor::class => ActorOrmMapping::class,
            Person::class => PersonOrmMapping::class,
            Organization::class => OrganizationOrmMapping::class,
            Subscription::class => SubscriptionOrmMapping::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getMappingValidation(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getMappingBus(): array
    {
        return [
            'command_handlers' => [
                CreateUserCommand::class => CreateUserCommandHandler::class,
                AssignActorCommand::class => AssignActorCommandHandler::class,
                SendObjectToRecipientsCommand::class => SendObjectToRecipientsCommandHandler::class,
                FollowCommand::class => FollowCommandHandler::class,
                UndoCommand::class => UndoCommandHandler::class,
                ValidateContentCommand::class => ValidateContentCommandHandler::class,

                PersistActivityStreamContentCommand::class => PersistActivityStreamContentCommandHandler::class,
                AttributeActivityStreamContentCommand::class => AttributeActivityStreamContentCommandHandler::class,
                AssignActivityStreamContentToFollowersCommand::class =>
                    AssignActivityStreamContentToFollowersCommandHandler::class,
            ],
            'event_handlers' => [
                ActivityStreamContentReceivedEvent::class => [
                    ActivityStreamContentReceivedEventHandler::class,
                ],
                ActivityStreamContentAttributedEvent::class => [
                    ActivityStreamContentAttributedEventHandler::class,
                ],
                ActivityStreamContentPersistedEvent::class => [
                    ActivityStreamContentPersistedEventHandler::class,
                ],
                ContentAcceptedEvent::class => [
                    ContentAcceptedEventHandler::class,
                ],
            ],
            'routing' => [
                /*ActivityStreamContentReceivedEvent::class => [
                    TransportInterface::class
                ],*/
            ],
        ];
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

    /**
     * @param array<string> $requiredEnvVariableNames
     * @return array<string, string>
     */
    private function getRequiredEnvVariables(array $requiredEnvVariableNames): array
    {
        $envVarValues = [];

        foreach ($requiredEnvVariableNames as $envVariableName) {
            if (null !== $value = $this->env->get($envVariableName)) {
                $envVarValues[$envVariableName] = $value;
            }
        }

        if (count($missingEnvVars = array_diff($requiredEnvVariableNames, array_keys($envVarValues))) > 0) {
            throw new \InvalidArgumentException(
                sprintf('Environment variables `%s` not set', implode('`, `', $missingEnvVars))
            );
        }

        return $envVarValues;
    }
}

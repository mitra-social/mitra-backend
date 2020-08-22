<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\RequestSignerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\MessageBus\EventBusInterface;
use Mitra\MessageBus\EventEmitter;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\MessageBus\Handler\Command\ActivityPub\AssignActivityStreamContentToActorCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\AssignActivityStreamContentToFollowersCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\AssignActorCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\AttributeActivityStreamContentCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\DereferenceCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\FollowCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\PersistActivityStreamContentCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\SendObjectToRecipientsCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\UndoCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\UpdateExternalActorCommandHandler;
use Mitra\MessageBus\Handler\Command\ActivityPub\ValidateContentCommandHandler;
use Mitra\MessageBus\Handler\Command\CreateUserCommandHandler;
use Mitra\MessageBus\Handler\Command\UpdateActorIconCommandHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\ActivityStreamContentAttributedEventHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\ActivityStreamContentPersistedEventHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\ActivityStreamContentReceivedEventHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\ContentAcceptedEventHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\DereferenceEventHandler;
use Mitra\MessageBus\Handler\Event\ActivityPub\ExternalUserUpdatedEventHandler;
use Mitra\MessageBus\SymfonyMessengerCommandBus;
use Mitra\MessageBus\SymfonyMessengerEventBus;
use Mitra\MessageBus\SymfonyMessengerHandlersLocator;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\MediaRepositoryInterface;
use Mitra\Repository\SubscriptionRepositoryInterface;
use Mitra\Slim\UriGeneratorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteResolverInterface;
use Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransportFactory;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransportFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransportFactory;
use Symfony\Component\Messenger\Transport\RedisExt\RedisTransportFactory;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportFactory;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class MessageBusServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $this->registerCommandHandlers($container);
        $this->registerEventHandlers($container);

        $container[EventEmitterInterface::class] = static function (Container $container): EventEmitterInterface {
            return new EventEmitter($container[EventBusInterface::class]);
        };

        $container[TransportFactoryInterface::class] = static function (
            Container $container
        ): TransportFactoryInterface {
            $factories = [
                new DoctrineTransportFactory($container['doctrine.orm.manager_registry']),
                new InMemoryTransportFactory(),
            ];

            if (extension_loaded('amqp')) {
                $factories[] = new AmqpTransportFactory();
            }

            if (extension_loaded('redis')) {
                $factories[] = new RedisTransportFactory();
            }

            return new TransportFactory($factories);
        };

        if (null !== $container['queue_dns']) {
            $container[TransportInterface::class] = static function (Container $container): TransportInterface {
                return $container[TransportFactoryInterface::class]->createTransport(
                    $container['queue_dns'],
                    new PhpSerializer()
                );
            };
        }

        // Messenger middlewares
        $container[SendMessageMiddleware::class] = static function (Container $container): SendMessageMiddleware {
            $sendersLocator = new SendersLocator(
                $container['mappings']['bus']['routing'],
                $container[PsrContainerInterface::class]
            );
            return new SendMessageMiddleware($sendersLocator);
        };

        // Command buses
        $container[EventBusInterface::class] = static function (Container $container): EventBusInterface {
            $eventHandlersLocator = new SymfonyMessengerHandlersLocator(
                $container[PsrContainerInterface::class],
                $container['mappings']['bus']['event_handlers']
            );

            $eventBus = new MessageBus([
                new DoctrineTransactionMiddleware($container['doctrine.orm.manager_registry']),
                $container[SendMessageMiddleware::class],
                new HandleMessageMiddleware($eventHandlersLocator, true),
            ]);

            return new SymfonyMessengerEventBus($eventBus);
        };

        $container[CommandBusInterface::class] = static function (Container $container): CommandBusInterface {
            $commandHandlersLocator = new SymfonyMessengerHandlersLocator(
                $container[PsrContainerInterface::class],
                $container['mappings']['bus']['command_handlers']
            );

            $commandBus = new MessageBus([
                new DispatchAfterCurrentBusMiddleware(),
                new DoctrineTransactionMiddleware($container['doctrine.orm.manager_registry']),
                $container[SendMessageMiddleware::class],
                new HandleMessageMiddleware($commandHandlersLocator, false),
            ]);

            return new SymfonyMessengerCommandBus($commandBus);
        };
    }

    private function registerCommandHandlers(Container $container): void
    {
        $container[CreateUserCommandHandler::class] = static function (Container $container): CreateUserCommandHandler {
            return new CreateUserCommandHandler($container[EntityManagerInterface::class]);
        };

        $container[AssignActorCommandHandler::class] = static function (
            Container $container
        ): AssignActorCommandHandler {
            return new AssignActorCommandHandler($container[UriGeneratorInterface::class]);
        };

        $container[SendObjectToRecipientsCommandHandler::class] = static function (
            Container $container
        ): SendObjectToRecipientsCommandHandler {
            return new SendObjectToRecipientsCommandHandler(
                $container[ActivityPubClientInterface::class],
                $container[RequestSignerInterface::class],
                $container[ExternalUserResolver::class],
                $container[UriGeneratorInterface::class],
                $container[LoggerInterface::class]
            );
        };

        $container[FollowCommandHandler::class] = static function (Container $container): FollowCommandHandler {
            return new FollowCommandHandler(
                $container[EntityManagerInterface::class],
                $container[ExternalUserResolver::class],
                $container[SubscriptionRepositoryInterface::class],
                $container[EventEmitterInterface::class]
            );
        };

        $container[UndoCommandHandler::class] = static function (Container $container): UndoCommandHandler {
            return new UndoCommandHandler(
                $container[ExternalUserResolver::class],
                $container[EntityManagerInterface::class],
                $container[SubscriptionRepositoryInterface::class]
            );
        };

        $container[AttributeActivityStreamContentCommandHandler::class] = static function (
            Container $container
        ): AttributeActivityStreamContentCommandHandler {
            return new AttributeActivityStreamContentCommandHandler(
                $container[EventEmitterInterface::class],
                $container[ExternalUserResolver::class],
                $container[EntityManagerInterface::class],
            );
        };

        $container[ValidateContentCommandHandler::class] = static function (
            Container $container
        ): ValidateContentCommandHandler {
            return new ValidateContentCommandHandler(
                $container[EventEmitterInterface::class],
                $container[SubscriptionRepositoryInterface::class]
            );
        };

        $container[PersistActivityStreamContentCommandHandler::class] = static function (
            Container $container
        ): PersistActivityStreamContentCommandHandler {
            return new PersistActivityStreamContentCommandHandler(
                $container[EntityManagerInterface::class],
                $container[ActivityStreamContentRepositoryInterface::class],
                $container[EventEmitterInterface::class]
            );
        };

        $container[AssignActivityStreamContentToFollowersCommandHandler::class] = static function (
            Container $container
        ): AssignActivityStreamContentToFollowersCommandHandler {
            /** @var UriFactoryInterface $uriFactory */
            $uriFactory = $container[UriFactoryInterface::class];

            return new AssignActivityStreamContentToFollowersCommandHandler(
                $container[SubscriptionRepositoryInterface::class],
                $container[InternalUserRepository::class],
                $container[EntityManagerInterface::class],
                $container[EventEmitterInterface::class],
                $uriFactory->createUri($container['baseUrl']),
                $container[RouteResolverInterface::class],
                $container[UriFactoryInterface::class],
                $container[ActivityPubClientInterface::class],
                $container[LoggerInterface::class]
            );
        };

        $container[AssignActivityStreamContentToActorCommandHandler::class] = static function (
            Container $container
        ): AssignActivityStreamContentToActorCommandHandler {
            return new AssignActivityStreamContentToActorCommandHandler(
                $container[EntityManagerInterface::class],
                $container[EventEmitterInterface::class]
            );
        };

        $container[UpdateExternalActorCommandHandler::class] = static function (
            Container $container
        ): UpdateExternalActorCommandHandler {
            return new UpdateExternalActorCommandHandler(
                $container[EventEmitterInterface::class],
                $container[RemoteObjectResolver::class],
                $container[ExternalUserResolver::class],
                $container[LoggerInterface::class]
            );
        };

        $container[UpdateActorIconCommandHandler::class] = static function (
            Container $container
        ): UpdateActorIconCommandHandler {
            return new UpdateActorIconCommandHandler(
                $container[RemoteObjectResolver::class],
                $container[HashGeneratorInterface::class],
                $container['api_http_client'],
                $container[RequestFactoryInterface::class],
                $container[FilesystemInterface::class],
                $container[LoggerInterface::class],
                $container[MediaRepositoryInterface::class],
                $container[EntityManagerInterface::class]
            );
        };

        $container[DereferenceCommandHandler::class] = static function (
            Container $container
        ): DereferenceCommandHandler {
            return new DereferenceCommandHandler(
                $container[EntityManagerInterface::class],
                $container[ActivityStreamContentFactoryInterface::class],
                $container[ActivityStreamContentRepositoryInterface::class],
                $container[RemoteObjectResolver::class],
                $container[EventEmitterInterface::class]
            );
        };
    }

    private function registerEventHandlers(Container $container): void
    {
        $container[ActivityStreamContentReceivedEventHandler::class] = static function (
            Container $container
        ): ActivityStreamContentReceivedEventHandler {
            return new ActivityStreamContentReceivedEventHandler($container[CommandBusInterface::class]);
        };

        $container[ActivityStreamContentAttributedEventHandler::class] = static function (
            Container $container
        ): ActivityStreamContentAttributedEventHandler {
            return new ActivityStreamContentAttributedEventHandler($container[CommandBusInterface::class]);
        };

        $container[ContentAcceptedEventHandler::class] = static function (
            Container $container
        ): ContentAcceptedEventHandler {
            return new ContentAcceptedEventHandler($container[CommandBusInterface::class]);
        };

        $container[ActivityStreamContentPersistedEventHandler::class] = static function (
            Container $container
        ): ActivityStreamContentPersistedEventHandler {
            return new ActivityStreamContentPersistedEventHandler($container[CommandBusInterface::class]);
        };

        $container[ExternalUserUpdatedEventHandler::class] = static function (
            Container $container
        ): ExternalUserUpdatedEventHandler {
            return new ExternalUserUpdatedEventHandler($container[CommandBusInterface::class]);
        };

        $container[DereferenceEventHandler::class] = static function (
            Container $container
        ): DereferenceEventHandler {
            return new DereferenceEventHandler($container[CommandBusInterface::class]);
        };
    }
}

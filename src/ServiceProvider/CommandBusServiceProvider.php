<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use League\Tactician\CommandBus;
use League\Tactician\Doctrine\ORM\TransactionMiddleware;
use League\Tactician\Handler\CommandHandlerMiddleware;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\EventDispatcher;
use Mitra\CommandBus\EventDispatcherInterface;
use Mitra\CommandBus\Handler\ActivityPub\AssignActorCommandHandler;
use Mitra\CommandBus\Handler\ActivityPub\FollowCommandHandler;
use Mitra\CommandBus\Handler\ActivityPub\SendObjectToRecipientsCommandHandler;
use Mitra\CommandBus\Handler\ActivityPub\UndoCommandHandler;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\CommandBus\SubscriptionResolver;
use Mitra\CommandBus\TacticianCommandBus;
use Mitra\CommandBus\TacticianEventMiddleware;
use Mitra\CommandBus\TacticianMapByStaticClassList;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Slim\UriGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class CommandBusServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $this->registerHandlers($container);

        $container[EventDispatcherInterface::class] = static function (Container $container): EventDispatcherInterface {
            return new EventDispatcher(new SubscriptionResolver(
                $container[ContainerInterface::class],
                $container['command_bus.event_subscribers']
            ));
        };

        $container[CommandBusInterface::class] = static function (Container $container): CommandBusInterface {
            $eventMiddleware = new TacticianEventMiddleware($container[EventDispatcherInterface::class]);

            $handlerMiddleware = new CommandHandlerMiddleware(
                $container[ContainerInterface::class],
                new TacticianMapByStaticClassList($container['mappings']['command_handlers'])
            );

            $transactionMiddleware = new TransactionMiddleware($container['doctrine.orm.em']);

            return new TacticianCommandBus(new CommandBus(
                $eventMiddleware,
                $transactionMiddleware,
                $handlerMiddleware
            ));
        };
    }

    private function registerHandlers(Container $container): void
    {
        $container[CreateUserCommandHandler::class] = static function (Container $container): CreateUserCommandHandler {
            return new CreateUserCommandHandler($container['doctrine.orm.em']);
        };

        $container[AssignActorCommandHandler::class] = static function (
            Container $container
        ): AssignActorCommandHandler {
            return new AssignActorCommandHandler($container[UriGenerator::class]);
        };

        $container[SendObjectToRecipientsCommandHandler::class] = static function (
            Container $container
        ): SendObjectToRecipientsCommandHandler {
            return new SendObjectToRecipientsCommandHandler(
                $container[ActivityPubClientInterface::class],
                $container[ExternalUserResolver::class],
                $container[UriGenerator::class],
                $container[LoggerInterface::class]
            );
        };

        $container[FollowCommandHandler::class] = static function (Container $container): FollowCommandHandler {
            return new FollowCommandHandler(
                $container['doctrine.orm.em'],
                $container[ExternalUserResolver::class],
                $container[SubscriptionRepository::class]
            );
        };

        $container[UndoCommandHandler::class] = static function (Container $container): UndoCommandHandler {
            return new UndoCommandHandler(
                $container[ExternalUserResolver::class],
                $container['doctrine.orm.em'],
                $container[SubscriptionRepository::class]
            );
        };
    }
}

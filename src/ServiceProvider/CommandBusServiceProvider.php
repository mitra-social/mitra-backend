<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\CommandBus\TacticianCommandBus;
use Mitra\CommandBus\TacticianMapByStaticClassList;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

final class CommandBusServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $this->registerHandlers($container);

        $container[CommandBusInterface::class] = function () use ($container): CommandBusInterface {
            $handlerMiddleware = new CommandHandlerMiddleware(
                $container[ContainerInterface::class],
                new TacticianMapByStaticClassList($container['mappings']['command_handlers'])
            );

            return new TacticianCommandBus(new CommandBus($handlerMiddleware));
        };
    }

    private function registerHandlers(Container $container): void
    {
        $container[CreateUserCommandHandler::class] = function () use ($container): CreateUserCommandHandler {
            return new CreateUserCommandHandler($container['doctrine.orm.em']);
        };
    }
}

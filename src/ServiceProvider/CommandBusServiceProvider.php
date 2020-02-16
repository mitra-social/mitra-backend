<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\CommandBus\TacticianCommandBus;
use Mitra\CommandBus\TacticianMapByStaticClassList;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Pimple\Psr11\Container as PsrContainer;

final class CommandBusServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $this->registerHandlers($container);

        $container[CommandBusInterface::class] = function () use ($container) {
            $handlerMiddleware = new CommandHandlerMiddleware(
                $container[PsrContainer::class],
                new TacticianMapByStaticClassList([
                    CreateUserCommand::class => CreateUserCommandHandler::class
                ])
            );

            return new TacticianCommandBus(new CommandBus($handlerMiddleware));
        };
    }

    private function registerHandlers(Container $container): void
    {
        $container[CreateUserCommandHandler::class] = function () use ($container) {
            return new CreateUserCommandHandler($container['doctrine.orm.em']);
        };
    }
}

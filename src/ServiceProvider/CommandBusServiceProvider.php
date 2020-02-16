<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use League\Tactician\CommandBus;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\TacticianCommandBus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class CommandBusServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[CommandBusInterface::class] = function () {
            return new TacticianCommandBus(new CommandBus());
        };
    }
}

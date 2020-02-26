<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

final class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container[LoggerInterface::class] = function ($container) {
            return new Logger($container['monolog.name'], [
                (new StreamHandler(
                    $container['monolog.path'],
                    $container['monolog.level']
                ))
            ]);
        };
    }
}

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
    public function register(Container $container): void
    {
        $container[LoggerInterface::class] = static function () use ($container): LoggerInterface {
            $handlers = [];

            foreach ($container['monolog.handlers'] as $path => $level) {
                $handlers[] = new StreamHandler($path, $level);
            }

            $logger = new Logger($container['monolog.name'], $handlers);

            $logger->pushProcessor(function ($record) {
                $record['extra']['request_id'] = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
                return $record;
            });

            return $logger;
        };

        // Register an alias as some 3rd-party service providers look for a `logger` key in the container
        $container['logger'] = static function () use ($container): LoggerInterface {
            return $container[LoggerInterface::class];
        };
    }
}

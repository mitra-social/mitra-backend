<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Logger\RequestContext;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

final class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[LoggerInterface::class] = static function (Container $container): LoggerInterface {
            $handlers = [];

            foreach ($container['monolog.handlers'] as $path => $level) {
                $handlers[] = new StreamHandler($path, $level);
            }

            $logger = new Logger($container['monolog.name'], $handlers);
            /** @var RequestContext $requestContext */
            $requestContext = $container[RequestContext::class];

            $logger->pushProcessor(function ($record) use ($requestContext) {
                $requestIdHeader = $requestContext->getRequest()->getHeaderLine('X-Request-Id');

                $record['extra']['request_id'] = '' !== $requestIdHeader ? $requestIdHeader : null;

                return $record;
            });

            return $logger;
        };

        // Register an alias as some 3rd-party service providers look for a `logger` key in the container
        $container['logger'] = static function () use ($container): LoggerInterface {
            return $container[LoggerInterface::class];
        };

        $container[RequestContext::class] = static function (): RequestContext {
            return new RequestContext();
        };
    }
}

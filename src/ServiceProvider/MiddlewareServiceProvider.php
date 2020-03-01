<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Middleware\RequestCycleCleanupMiddleware;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Tuupola\Middleware\JwtAuthentication;

final class MiddlewareServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[RequestCycleCleanupMiddleware::class] = function () use ($container): RequestCycleCleanupMiddleware {
            return new RequestCycleCleanupMiddleware(
                $container['doctrine.orm.em'],
                $container[LoggerInterface::class]
            );
        };

        $container[JwtAuthentication::class] = static function () use ($container): JwtAuthentication {
            return new JwtAuthentication([
                'path' => '/',
                'ignore' => [],
                'secret' => $container['jwt.secret'],
                'logger' => $container[LoggerInterface::class],
            ]);
        };
    }
}

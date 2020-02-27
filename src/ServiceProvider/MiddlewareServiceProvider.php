<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use ActivityPhp\Server;
use ActivityPhp\Type\TypeResolver;
use ActivityPhp\TypeFactory;
use ActivityPhp\Server\Http\GuzzleActivityPubClient;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\Middleware\RequestCycleCleanupMiddleware;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseFactoryInterface;
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

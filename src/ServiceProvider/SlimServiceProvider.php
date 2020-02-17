<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Slim\CallableResolver;
use Slim\Handlers\Strategies\RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;

final class SlimServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[CallableResolver::class] = function () use ($container) {
            return new CallableResolver($container[ContainerInterface::class]);
        };

        $container[ResponseFactory::class] = function () {
            return new ResponseFactory();
        };

        $container[RouteCollector::class] = function () use ($container) {
            return new RouteCollector(
                $container[ResponseFactory::class],
                $container[CallableResolver::class],
                $container[ContainerInterface::class],
                new RequestHandler(true),
                null,
                $container['routerCacheFile']
            );
        };
    }
}

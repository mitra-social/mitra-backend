<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\ActivityPub\HashGenerator;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Clock\Clock;
use Mitra\Clock\ClockInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Slim\UriGeneratorInterface;
use Mitra\Slim\UuidGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Slim\CallableResolver;
use Slim\Handlers\Strategies\RequestHandler;
use Slim\Interfaces\AdvancedCallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;
use Slim\Routing\RouteResolver;

final class SlimServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[CallableResolver::class] = function () use ($container): AdvancedCallableResolverInterface {
            return new CallableResolver($container[ContainerInterface::class]);
        };

        $container[ResponseFactory::class] = function (): ResponseFactoryInterface {
            return new ResponseFactory();
        };

        $container[RouteCollector::class] = function () use ($container): RouteCollectorInterface {
            return new RouteCollector(
                $container[ResponseFactory::class],
                $container[CallableResolver::class],
                $container[ContainerInterface::class],
                new RequestHandler(true),
                null,
                $container['routerCacheFile']
            );
        };

        $container[RouteResolverInterface::class] = function () use ($container): RouteResolverInterface {
            return new RouteResolver($container[RouteCollector::class]);
        };

        $container[UriGeneratorInterface::class] = static function (Container $container): UriGeneratorInterface {
            /** @var UriFactoryInterface $uriFactory */
            $uriFactory = $container[UriFactoryInterface::class];
            /** @var RouteCollectorInterface $routeCollector */
            $routeCollector = $container[RouteCollector::class];

            return new UriGenerator($uriFactory->createUri($container['baseUrl']), $routeCollector->getRouteParser());
        };

        $container[HashGeneratorInterface::class] = static function (): HashGeneratorInterface {
            return new HashGenerator('md5');
        };

        $container[IdGeneratorInterface::class] = static function (): IdGeneratorInterface {
            return new UuidGenerator();
        };

        $container[ClockInterface::class] = static function (): ClockInterface {
            return new Clock();
        };
    }
}

<?php

declare(strict_types=1);

namespace Mitra;

use Mitra\Controller\System\PingController;
use Mitra\ServiceProvider\SlimServiceProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\CallableResolver;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;
use Pimple\Psr11\Container as PsrContainer;

final class AppFactory
{
    /**
     * @param string $environment
     * @return App
     */
    public function create(string $environment): App
    {
        $app = $this->createApp($environment);

        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        $app->addErrorMiddleware($container->get('debug'), true, true);
        $app->get('/ping', PingController::class)
            ->setName('ping');

        return $app;
    }

    /**
     * @param string $environment
     * @return App
     */
    protected function createApp(string $environment): App
    {
        $container = AppContainer::init($environment);
        $container->register(new SlimServiceProvider());

        return new App(
            $container[ResponseFactory::class],
            $container[PsrContainer::class],
            $container[CallableResolver::class],
            $container[RouteCollector::class]
        );
    }
}
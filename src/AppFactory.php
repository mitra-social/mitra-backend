<?php

declare(strict_types=1);

namespace Mitra;

use Mitra\Controller\System\PingController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Env\Env;
use Mitra\ServiceProvider\SlimServiceProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\CallableResolver;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;

final class AppFactory
{
    /**
     * @param Env $env
     * @return App
     */
    public function create(Env $env): App
    {
        $app = $this->createApp($env);

        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        $app->addErrorMiddleware($container->get('debug'), true, true);
        $app->get('/ping', PingController::class)->setName('ping');
        $app->post('/user', CreateUserController::class)->setName('user-create');

        return $app;
    }

    /**
     * @param Env $env
     * @return App
     */
    private function createApp(Env $env): App
    {
        $container = AppContainer::init($env);
        $container->register(new SlimServiceProvider());

        return new App(
            $container[ResponseFactory::class],
            $container[ContainerInterface::class],
            $container[CallableResolver::class],
            $container[RouteCollector::class]
        );
    }
}

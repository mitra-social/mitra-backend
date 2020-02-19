<?php

declare(strict_types=1);

namespace Mitra;

use Mitra\Controller\System\PingController;
use Mitra\Controller\User\CreateUserController;
use Mitra\ServiceProvider\SlimServiceProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\CallableResolver;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;

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
        $app->get('/ping', PingController::class)->setName('ping');
        $app->post('/user', CreateUserController::class)->setName('user-create');

        return $app;
    }

    /**
     * @param string $environment
     * @return App
     */
    private function createApp(string $environment): App
    {
        $container = AppContainer::init($environment);
        $container->register(new SlimServiceProvider());

        return new App(
            $container[ResponseFactory::class],
            $container[ContainerInterface::class],
            $container[CallableResolver::class],
            $container[RouteCollector::class]
        );
    }
}

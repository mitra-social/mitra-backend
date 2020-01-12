<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Controller\System\PingController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Psr7\Factory\ResponseFactory;

final class ControllerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[PingController::class] = function () use ($container) {
            return new PingController($container[ResponseFactory::class]);
        };
    }
}

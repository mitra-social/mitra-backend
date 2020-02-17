<?php

declare(strict_types=1);

namespace Mitra;

use Chubbyphp\Config\ServiceProvider\ConfigServiceProvider;
use Chubbyphp\DoctrineDbServiceProvider\ServiceProvider\DoctrineDbalServiceProvider;
use Chubbyphp\DoctrineDbServiceProvider\ServiceProvider\DoctrineOrmServiceProvider;
use Mitra\Config\Config;
use Mitra\ServiceProvider\CommandBusServiceProvider;
use Mitra\ServiceProvider\ControllerServiceProvider;
use Mitra\ServiceProvider\DataToDtoServiceProvider;
use Mitra\ServiceProvider\DoctrineServiceProvider;
use Mitra\ServiceProvider\ProxyManagerServiceProvider;
use Mitra\ServiceProvider\SerializationServiceProvider;
use Mitra\ServiceProvider\ValidatorServiceProvider;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Container\ContainerInterface;

final class AppContainer
{

    /**
     * @param string $env
     * @return Container
     */
    public static function init(string $env): Container
    {
        $container = new Container(['env' => $env]);

        // Config
        $container->register(new ConfigServiceProvider(new Config(__DIR__ . '/..')));

        // Psr11 container decorator
        $container[ContainerInterface::class] = function () use ($container) {
            return new PsrContainer($container);
        };

        // Third party
        $container
            ->register(new DoctrineOrmServiceProvider())
            ->register(new DoctrineDbalServiceProvider());

        // Own
        $container
            ->register(new SerializationServiceProvider())
            ->register(new CommandBusServiceProvider())
            ->register(new ValidatorServiceProvider())
            ->register(new DoctrineServiceProvider())
            ->register(new ProxyManagerServiceProvider())
            ->register(new DataToDtoServiceProvider())
            ->register(new ControllerServiceProvider())
        ;

        return $container;
    }
}

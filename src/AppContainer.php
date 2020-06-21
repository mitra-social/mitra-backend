<?php

declare(strict_types=1);

namespace Mitra;

use Chubbyphp\Config\ServiceProvider\ConfigServiceProvider;
use Chubbyphp\DoctrineDbServiceProvider\ServiceProvider\DoctrineDbalServiceProvider;
use Chubbyphp\DoctrineDbServiceProvider\ServiceProvider\DoctrineOrmServiceProvider;
use Mitra\Config\Config;
use Mitra\Env\Env;
use Mitra\ServiceProvider\ActivityPubServiceProvider;
use Mitra\ServiceProvider\AuthenticationServiceProvider;
use Mitra\ServiceProvider\CommandBusServiceProvider;
use Mitra\ServiceProvider\DtoServiceProvider;
use Mitra\ServiceProvider\DoctrineServiceProvider;
use Mitra\ServiceProvider\FilesystemServiceProvider;
use Mitra\ServiceProvider\FilteringServiceProvider;
use Mitra\ServiceProvider\HttpClientServiceProvider;
use Mitra\ServiceProvider\HttpServiceProvider;
use Mitra\ServiceProvider\MonologServiceProvider;
use Mitra\ServiceProvider\ProxyManagerServiceProvider;
use Mitra\ServiceProvider\RepositoryServiceProvider;
use Mitra\ServiceProvider\SerializationServiceProvider;
use Mitra\ServiceProvider\ValidatorServiceProvider;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Container\ContainerInterface;

final class AppContainer
{

    /**
     * @param Env $env
     * @return Container
     */
    public static function init(Env $env): Container
    {
        $container = new Container();

        // Config
        $container->register(new ConfigServiceProvider(new Config(__DIR__ . '/..', $env)));

        // Psr11 container decorator
        $container[ContainerInterface::class] = function () use ($container): PsrContainer {
            return new PsrContainer($container);
        };

        // Third party
        $container
            ->register(new DoctrineOrmServiceProvider())
            ->register(new DoctrineDbalServiceProvider())
        ;

        // Own
        $container
            ->register(new HttpServiceProvider())
            ->register(new SerializationServiceProvider())
            ->register(new CommandBusServiceProvider())
            ->register(new ActivityPubServiceProvider())
            ->register(new ValidatorServiceProvider())
            ->register(new FilteringServiceProvider())
            ->register(new DoctrineServiceProvider())
            ->register(new ProxyManagerServiceProvider())
            ->register(new DtoServiceProvider())
            ->register(new RepositoryServiceProvider())
            ->register(new MonologServiceProvider())
            ->register(new AuthenticationServiceProvider())
            ->register(new HttpClientServiceProvider())
            ->register(new FilesystemServiceProvider())
        ;

        return $container;
    }
}

<?php

declare(strict_types=1);

namespace Mitra;

use Chubbyphp\Config\ConfigProvider;
use Chubbyphp\Config\ServiceProvider\ConfigServiceProvider;
use Mitra\Config\DevConfig;
use Mitra\ServiceProvider\CommandBusServiceProvider;
use Mitra\ServiceProvider\ControllerServiceProvider;
use Mitra\ServiceProvider\SerializationServiceProvider;
use Mitra\ServiceProvider\ValidatorServiceProvider;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

final class AppContainer
{

    /**
     * @param string $env
     * @return Container
     */
    public static function init(string $env): Container
    {
        $container = new Container(['env' => $env]);

        $container[PsrContainer::class] = function () use ($container) {
            return new PsrContainer($container);
        };

        // Third party


        // Own
        $container
            ->register(new SerializationServiceProvider())
            ->register(new CommandBusServiceProvider())
            ->register(new ValidatorServiceProvider())
            ->register(new ControllerServiceProvider())
            ->register(new ConfigServiceProvider(
                (new ConfigProvider([
                    new DevConfig(__DIR__.'/..'),
                ]))->get($env)
            ))
        ;


        //Always keep that provider at the end


        return $container;
    }
}

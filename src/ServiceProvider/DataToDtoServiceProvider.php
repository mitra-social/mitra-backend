<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Dto\DataToDtoManager;
use Mitra\Dto\DataToDtoPopulator;
use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

final class DataToDtoServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container[DataToDtoPopulator::class . NestedDto::class] = function () {
            return new DataToDtoPopulator(NestedDto::class);
        };

        $container[DataToDtoPopulator::class . UserDto::class] = function () use ($container) {
            return (new DataToDtoPopulator(UserDto::class))
                ->map('nested', $container[DataToDtoPopulator::class . NestedDto::class]);
        };

        $container[DataToDtoManager::class] = function () use ($container) {
            return new DataToDtoManager($container[ContainerInterface::class], [
                UserDto::class => DataToDtoPopulator::class . UserDto::class,
                NestedDto::class => DataToDtoPopulator::class . NestedDto::class,
            ]);
        };
    }
}

<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Dto\DataToDtoManager;
use Mitra\Dto\DataToDtoPopulator;
use Mitra\Dto\EntityToDtoManager;
use Mitra\Dto\EntityToDtoPopulator;
use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\Request\TokenRequestDto;
use Mitra\Dto\RequestToDtoManager;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Serialization\Decode\DecoderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

final class DtoServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $container): void
    {
        $this->registerDataToDtoPopulators($container);

        $container[DataToDtoManager::class] = function () use ($container): DataToDtoManager {
            return new DataToDtoManager($container[ContainerInterface::class], [
                CreateUserRequestDto::class => DataToDtoPopulator::class . CreateUserRequestDto::class,
                TokenRequestDto::class => DataToDtoPopulator::class . TokenRequestDto::class,
            ]);
        };

        $container[RequestToDtoManager::class] = function () use ($container): RequestToDtoManager {
            return new RequestToDtoManager($container[DataToDtoManager::class], $container[DecoderInterface::class]);
        };

        $this->registerEntityToDtoPopulators($container);

        $container[EntityToDtoManager::class] = function () use ($container): EntityToDtoManager {
            return new EntityToDtoManager($container[ContainerInterface::class], [
                UserResponseDto::class => EntityToDtoPopulator::class . UserResponseDto::class,
            ]);
        };
    }

    private function registerDataToDtoPopulators(Container $container): void
    {
        $container[DataToDtoPopulator::class . CreateUserRequestDto::class] = function (): DataToDtoPopulator {
            return new DataToDtoPopulator(CreateUserRequestDto::class);
        };

        $container[DataToDtoPopulator::class . TokenRequestDto::class] = function (): DataToDtoPopulator {
            return new DataToDtoPopulator(TokenRequestDto::class);
        };
    }

    private function registerEntityToDtoPopulators(Container $container): void
    {
        $container[EntityToDtoPopulator::class . UserResponseDto::class] = function (): EntityToDtoPopulator {
            return (new EntityToDtoPopulator(UserResponseDto::class))
                ->mapProperty('registeredAt', 'createdAt')
            ;
        };
    }
}

<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\CommandBus\CommandBusInterface;
use Mitra\Controller\System\PingController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Dto\DataToDtoManager;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
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

        $container[CreateUserController::class] = function () use ($container) {
            return new CreateUserController(
                $container[ResponseFactory::class],
                $container[EncoderInterface::class],
                $container[DecoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[DataToDtoManager::class]
            );
        };
    }
}

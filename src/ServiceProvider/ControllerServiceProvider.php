<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Authentication\TokenProvider;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Controller\ActivityPub\InboxController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\System\PingController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Controller\Webfinger\WebfingerController;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\RequestToDtoManager;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\UserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ValidatorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Routing\RouteCollector;

final class ControllerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        // Public
        $container[PingController::class] = function () use ($container): PingController {
            return new PingController($container[ResponseFactoryInterface::class]);
        };

        $container[TokenController::class] = function () use ($container): TokenController {
            return new TokenController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[TokenProvider::class],
                $container[RequestToDtoManager::class]
            );
        };

        $container[CreateUserController::class] = function () use ($container): CreateUserController {
            return new CreateUserController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[RequestToDtoManager::class],
                $container[DtoToEntityMapper::class]
            );
        };

        $container[InboxController::class] = static function (Container $container): InboxController {
            return new InboxController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[UserRepository::class],
                $container[RouteCollector::class]
            );
        };

        $container[WebfingerController::class] = function () use ($container): WebfingerController {
            return new WebfingerController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[UserRepository::class]
            );
        };

        // Private
        $container[ProfileController::class] = function () use ($container): ProfileController {
            return new ProfileController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[UserRepository::class]
            );
        };
    }
}

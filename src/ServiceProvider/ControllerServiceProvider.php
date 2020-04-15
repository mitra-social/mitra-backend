<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Authentication\TokenProvider;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Controller\User\InboxReadController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\System\PingController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Controller\User\InboxWriteController;
use Mitra\Controller\User\OutboxController;
use Mitra\Controller\User\ReadUserController;
use Mitra\Controller\Webfinger\WebfingerController;
use Mitra\Dto\DataToDtoTransformer;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\InternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Validator\ValidatorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
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
        $container[PingController::class] = static function (Container $container): PingController {
            return new PingController($container[ResponseFactoryInterface::class]);
        };

        $container[TokenController::class] = static function (Container $container): TokenController {
            return new TokenController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[TokenProvider::class],
                $container[RequestToDtoTransformer::class]
            );
        };

        $container[CreateUserController::class] = static function (Container $container): CreateUserController {
            return new CreateUserController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[RequestToDtoTransformer::class],
                $container[DtoToEntityMapper::class]
            );
        };

        $container[ReadUserController::class] = static function (Container $container): ReadUserController {
            return new ReadUserController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class]
            );
        };

        $container[InboxReadController::class] = static function (Container $container): InboxReadController {
            return new InboxReadController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class],
                $container[ActivityStreamContentAssignmentRepository::class],
                $container[RouteCollector::class],
                $container[DataToDtoTransformer::class]
            );
        };

        $container[OutboxController::class] = static function (Container $container): OutboxController {
            return new OutboxController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[ActivityPubDtoPopulator::class],
                $container[DecoderInterface::class],
                $container[DtoToEntityMapper::class],
                $container[InternalUserRepository::class]
            );
        };

        $container[WebfingerController::class] = static function (Container $container): WebfingerController {
            return new WebfingerController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class],
                $container[UriGenerator::class]
            );
        };

        $container[InboxWriteController::class] = static function (Container $container): InboxWriteController {
            return new InboxWriteController(
                $container[ResponseFactoryInterface::class],
                $container[LoggerInterface::class]
            );
        };

        // Private
        $container[ProfileController::class] = static function (Container $container): ProfileController {
            return new ProfileController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class]
            );
        };
    }
}

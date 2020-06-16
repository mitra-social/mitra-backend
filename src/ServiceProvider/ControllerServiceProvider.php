<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use League\Flysystem\FilesystemInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Authentication\TokenProvider;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\EventBusInterface;
use Mitra\Controller\System\MediaController;
use Mitra\Controller\User\FollowingListController;
use Mitra\Controller\User\InboxReadController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\System\PingController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Controller\User\InboxWriteController;
use Mitra\Controller\User\OutboxWriteController;
use Mitra\Controller\User\UserReadController;
use Mitra\Controller\Webfinger\WebfingerController;
use Mitra\Dto\DataToDtoTransformer;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\MediaRepositoryInterface;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Validator\ValidatorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

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

        $container[UserReadController::class] = static function (Container $container): UserReadController {
            return new UserReadController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class]
            );
        };

        $container[InboxReadController::class] = static function (Container $container): InboxReadController {
            return new InboxReadController(
                $container[ResponseFactoryInterface::class],
                $container[InternalUserRepository::class],
                $container[ActivityStreamContentAssignmentRepository::class],
                $container[UriGenerator::class],
                $container[DataToDtoTransformer::class],
                $container[EntityToDtoMapper::class]
            );
        };

        $container[OutboxWriteController::class] = static function (Container $container): OutboxWriteController {
            return new OutboxWriteController(
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
                $container[NormalizerInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[EventBusInterface::class],
                $container[ActivityPubDtoPopulator::class],
                $container[DecoderInterface::class],
                $container[DtoToEntityMapper::class],
                $container[InternalUserRepository::class],
                $container[ActivityStreamContentRepositoryInterface::class],
                $container[HashGeneratorInterface::class],
                $container[LoggerInterface::class]
            );
        };

        $container[FollowingListController::class] = static function (Container $container): FollowingListController {
            return new FollowingListController(
                $container[SubscriptionRepository::class],
                $container[InternalUserRepository::class],
                $container[UriGenerator::class],
                $container[ResponseFactoryInterface::class],
                $container[EntityToDtoMapper::class]
            );
        };

        $container[MediaController::class] = static function (Container $container): MediaController {
            return new MediaController(
                $container[ResponseFactoryInterface::class],
                $container[MediaRepositoryInterface::class],
                $container[FilesystemInterface::class]
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

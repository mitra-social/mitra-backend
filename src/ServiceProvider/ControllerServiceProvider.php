<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Authentication\TokenProvider;
use Mitra\Controller\User\UserUpdateController;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\MessageBus\EventBusInterface;
use Mitra\Controller\System\MediaController;
use Mitra\Controller\System\SharedInboxWriteController;
use Mitra\Controller\User\ActivityReadController;
use Mitra\Controller\User\FollowingListController;
use Mitra\Controller\User\InboxReadController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\System\PingController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\UserCreateController;
use Mitra\Controller\User\InboxWriteController;
use Mitra\Controller\System\InstanceUserReadController;
use Mitra\Controller\User\OutboxWriteController;
use Mitra\Controller\User\UserReadController;
use Mitra\Controller\Webfinger\WebfingerController;
use Mitra\Dto\DtoToEntityMapper;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\RequestToDtoTransformer;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Filtering\FilterFactoryInterface;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\MediaRepositoryInterface;
use Mitra\Repository\SubscriptionRepositoryInterface;
use Mitra\Security\PasswordVerifier;
use Mitra\Security\PasswordVerifierInterface;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Slim\UriGeneratorInterface;
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

        $container[ActivityReadController::class] = static function (
            Container $container
        ): ActivityReadController {
            return new ActivityReadController(
                $container[ResponseFactoryInterface::class]
            );
        };

        $container[SharedInboxWriteController::class] = static function (
            Container $container
        ): SharedInboxWriteController {
            return new SharedInboxWriteController(
                $container[ResponseFactoryInterface::class],
                $container[NormalizerInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[EventBusInterface::class],
                $container[ActivityPubDtoPopulator::class],
                $container[DecoderInterface::class],
                $container[DtoToEntityMapper::class],
                $container[ActivityStreamContentRepositoryInterface::class],
                $container[HashGeneratorInterface::class],
                $container[LoggerInterface::class]
            );
        };

        $container[UserCreateController::class] = static function (Container $container): UserCreateController {
            return new UserCreateController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[RequestToDtoTransformer::class],
                $container[DtoToEntityMapper::class]
            );
        };

        $container[UserUpdateController::class] = static function (Container $container): UserUpdateController {
            return new UserUpdateController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[CommandBusInterface::class],
                $container[RequestToDtoTransformer::class],
                $container[DtoToEntityMapper::class],
                $container[InternalUserRepository::class],
                $container[PasswordVerifierInterface::class]
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
                $container[FilterFactoryInterface::class],
                $container[InternalUserRepository::class],
                $container[ActivityStreamContentAssignmentRepositoryInterface::class],
                $container[UriGeneratorInterface::class],
                $container[EntityToDtoMapper::class],
                $container[ActivityPubDtoPopulator::class]
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
                $container[InternalUserRepository::class],
                $container[UriGeneratorInterface::class],
                $container[IdGeneratorInterface::class]
            );
        };

        $container[WebfingerController::class] = static function (Container $container): WebfingerController {
            return new WebfingerController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[InternalUserRepository::class],
                $container[UriGeneratorInterface::class]
            );
        };

        $container[InboxWriteController::class] = static function (Container $container): InboxWriteController {
            return new InboxWriteController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[ValidatorInterface::class],
                $container[EventBusInterface::class],
                $container[ActivityPubDtoPopulator::class],
                $container[DecoderInterface::class],
                $container[DtoToEntityMapper::class],
                $container[InternalUserRepository::class],
                $container[ActivityStreamContentFactoryInterface::class],
                $container[ActivityStreamContentRepositoryInterface::class],
                $container[LoggerInterface::class],
                $container[EntityManagerInterface::class]
            );
        };

        $container[FollowingListController::class] = static function (Container $container): FollowingListController {
            return new FollowingListController(
                $container[SubscriptionRepositoryInterface::class],
                $container[InternalUserRepository::class],
                $container[UriGeneratorInterface::class],
                $container[ResponseFactoryInterface::class],
                $container[FilterFactoryInterface::class],
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

        $container[InstanceUserReadController::class] = static function (
            Container $container
        ): InstanceUserReadController {
            return new InstanceUserReadController(
                $container[ResponseFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[UriGeneratorInterface::class],
                $container['instanceUser']
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

<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Media;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Repository\ActivityStreamContentRepository;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\MediaRepository;
use Mitra\Repository\MediaRepositoryInterface;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Repository\SubscriptionRepositoryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class RepositoryServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[InternalUserRepository::class] = static function (Container $container): InternalUserRepository {
            return new InternalUserRepository(
                $container[EntityManagerInterface::class]->getRepository(InternalUser::class)
            );
        };

        $container[ExternalUserRepository::class] = static function (Container $container): ExternalUserRepository {
            return new ExternalUserRepository(
                $container[EntityManagerInterface::class]->getRepository(ExternalUser::class)
            );
        };

        $container[ActivityStreamContentAssignmentRepositoryInterface::class] = static function (
            Container $container
        ): ActivityStreamContentAssignmentRepositoryInterface {
            return new ActivityStreamContentAssignmentRepository(
                $container[EntityManagerInterface::class]
            );
        };

        $container[ActivityStreamContentRepositoryInterface::class] = static function (
            Container $container
        ): ActivityStreamContentRepositoryInterface {
            return new ActivityStreamContentRepository(
                $container[EntityManagerInterface::class],
                $container[HashGeneratorInterface::class]
            );
        };

        $container[SubscriptionRepositoryInterface::class] = static function (
            Container $container
        ): SubscriptionRepositoryInterface {
            return new SubscriptionRepository(
                $container[EntityManagerInterface::class]
            );
        };

        $container[MediaRepositoryInterface::class] = static function (Container $container): MediaRepositoryInterface {
            return new MediaRepository(
                $container[EntityManagerInterface::class]
            );
        };
    }
}

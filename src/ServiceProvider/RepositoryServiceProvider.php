<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
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
            return new InternalUserRepository($container['doctrine.orm.em']->getRepository(InternalUser::class));
        };

        $container[ExternalUserRepository::class] = static function (Container $container): ExternalUserRepository {
            return new ExternalUserRepository($container['doctrine.orm.em']->getRepository(ExternalUser::class));
        };

        $container[ActivityStreamContentAssignmentRepository::class] = static function (Container $container) {
            return $container['doctrine.orm.em']->getRepository(ActivityStreamContentAssignment::class);
        };

        $container[SubscriptionRepository::class] = static function (Container $container): SubscriptionRepository {
            return new SubscriptionRepository($container['doctrine.orm.em']->getRepository(Subscription::class));
        };
    }
}

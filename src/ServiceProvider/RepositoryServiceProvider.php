<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Repository\UserRepository;
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
        $container[UserRepository::class] = function ($container) {
            return $container['doctrine.orm.em']->getRepository(User::class);
        };

        $container[ActivityStreamContentAssignmentRepository::class] = function ($container) {
            return $container['doctrine.orm.em']->getRepository(ActivityStreamContentAssignment::class);
        };
    }
}

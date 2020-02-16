<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\ORM\Configuration as DoctrineConfiguration;
use Doctrine\ORM\EntityManager;
use Mitra\Mapping\Orm\UserOrmMapping;
use Mitra\Entity\User;
use Mitra\Orm\EntityManagerDecorator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container A container instance
     * @return void
     */
    public function register(Container $container)
    {
        $container['doctrine.orm.em.factory'] = $container->protect(
            function (Connection $connection, DoctrineConfiguration $config, EventManager $eventManager) {
                return new EntityManagerDecorator(EntityManager::create($connection, $config, $eventManager));
            }
        );

        $container['doctrine.orm.em.options'] = [
            'mappings' => [
                [
                    'type' => 'class_map',
                    'namespace' => 'Mitra\Entity',
                    'map' => [
                        User::class => UserOrmMapping::class,
                    ]
                ]
            ],
        ];

        // Doctrine migrations configuration
        $container[Configuration::class] = function ($container) {
            $configuration = new Configuration($container['doctrine.orm.em']->getConnection());
            $configuration->setMigrationsTableName($container['doctrine.migrations.table']);
            $configuration->setMigrationsDirectory($container['doctrine.migrations.directory']);
            $configuration->setMigrationsNamespace($container['doctrine.migrations.namespace']);
            $configuration->registerMigrationsFromDirectory($container['doctrine.migrations.directory']);

            return $configuration;
        };
    }
}

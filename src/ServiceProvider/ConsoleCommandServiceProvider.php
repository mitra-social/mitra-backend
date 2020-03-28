<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Chubbyphp\DoctrineDbServiceProvider\Command\CreateDatabaseDoctrineCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\DropDatabaseDoctrineCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\CollectionRegionCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\EntityRegionCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\MetadataCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\QueryCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\QueryRegionCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ClearCache\ResultCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\EnsureProductionSettingsCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\InfoCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\RunDqlCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\SchemaTool\CreateCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\SchemaTool\DropCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\SchemaTool\UpdateCommand;
use Chubbyphp\DoctrineDbServiceProvider\Command\Orm\ValidateSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Mitra\Command\FixtureLoadCommand;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class ConsoleCommandServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container A container instance
     * @return void
     */
    public function register(Container $container): void
    {
        // Put your commands here
        $container['console.commands'] = function ($container): array {
            $ormManagerRegistry = $container['proxymanager.doctrine.orm.manager_registry'];
            $dbalConnectionRegistry = $container['proxymanager.doctrine.dbal.connection_registry'];

            $commands = [
                // own

                // doctrine dbal
                new CreateDatabaseDoctrineCommand($dbalConnectionRegistry),
                new DropDatabaseDoctrineCommand($dbalConnectionRegistry),
                // doctrine orm
                new CollectionRegionCommand($ormManagerRegistry),
                new EntityRegionCommand($ormManagerRegistry),
                new MetadataCommand($ormManagerRegistry),
                new QueryCommand($ormManagerRegistry),
                new QueryRegionCommand($ormManagerRegistry),
                new ResultCommand($ormManagerRegistry),
                new CreateCommand($ormManagerRegistry),
                new UpdateCommand($ormManagerRegistry),
                new DropCommand($ormManagerRegistry),
                new EnsureProductionSettingsCommand($ormManagerRegistry),
                new InfoCommand($ormManagerRegistry),
                new RunDqlCommand($ormManagerRegistry),
                new ValidateSchemaCommand($ormManagerRegistry),
                new GenerateProxiesCommand(),
                // doctrine migrations
                new DiffCommand(),
                new ExecuteCommand(),
                new GenerateCommand(),
                new LatestCommand(),
                new MigrateCommand(),
                new StatusCommand(),
            ];

            if ('dev' === $container['env']) {
                $commands[] = new FixtureLoadCommand($container['doctrine.orm.em']);
            }

            return $commands;
        };
    }
}

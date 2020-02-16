<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

final class ProxyManagerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['proxymanager.factory'] = function () {
            return new LazyLoadingValueHolderFactory();
        };

        $container['proxymanager.doctrine.dbal.connection_registry'] = function ($container) {
            return $container['proxymanager.factory']->createProxy(
                ConnectionRegistry::class,
                function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($container) {
                    $wrappedObject = $container['doctrine.dbal.connection_registry'];
                    $initializer = null;
                }
            );
        };

        $container['proxymanager.doctrine.orm.manager_registry'] = function ($container) {
            return $container['proxymanager.factory']->createProxy(
                ManagerRegistry::class,
                function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($container) {
                    $wrappedObject = $container['doctrine.orm.manager_registry'];
                    $initializer = null;
                }
            );
        };
    }
}

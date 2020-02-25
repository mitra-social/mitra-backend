<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\VirtualProxyInterface;

final class ProxyManagerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container['proxymanager.factory'] = function (): AbstractBaseFactory {
            return new LazyLoadingValueHolderFactory();
        };

        $container['proxymanager.doctrine.dbal.connection_registry'] = function ($container): VirtualProxyInterface {
            return $container['proxymanager.factory']->createProxy(
                ConnectionRegistry::class,
                function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($container): void {
                    $wrappedObject = $container['doctrine.dbal.connection_registry'];
                    $initializer = null;
                }
            );
        };

        $container['proxymanager.doctrine.orm.manager_registry'] = function ($container): VirtualProxyInterface {
            return $container['proxymanager.factory']->createProxy(
                ManagerRegistry::class,
                function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($container): void {
                    $wrappedObject = $container['doctrine.orm.manager_registry'];
                    $initializer = null;
                }
            );
        };
    }
}

<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Filtering\FilterFactory;
use Mitra\Filtering\FilterFactoryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class FilteringServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[FilterFactoryInterface::class] = function (): FilterFactoryInterface {
            return new FilterFactory();
        };
    }
}

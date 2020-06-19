<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Tests\Helper\ActivityPub\ActivityPubTestClient;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
use Mitra\Tests\Helper\Http\MockClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class TestServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container['api_http_client'] = function () {
            return new MockClient();
        };

        $originalFactory = $container->raw(ActivityPubClientInterface::class);

        $container[ActivityPubClientInterface::class] = function (Container $container) use ($originalFactory) {
            return new ActivityPubTestClient($originalFactory($container));
        };

        $container[IdGeneratorInterface::class] =  new ReflectedIdGenerator();

    }
}

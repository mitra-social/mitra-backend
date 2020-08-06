<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\ActivityPub\RequestSigner;
use Mitra\ActivityPub\RequestSignerInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
use Mitra\Tests\Helper\Http\MockClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

final class TestServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container['api_http_client'] = function () {
            return new MockClient();
        };

        $container[RequestSignerInterface::class] = static function (
            Container $container
        ): RequestSignerInterface {
            return new RequestSigner(
                $container[UriGenerator::class],
                $container['instance']['privateKey'],
                $container[LoggerInterface::class],
                ['Host', 'Accept']
            );
        };

        $container[IdGeneratorInterface::class] =  new ReflectedIdGenerator();
    }
}

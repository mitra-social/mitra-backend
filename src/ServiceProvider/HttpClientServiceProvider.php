<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Buzz\Client\Curl;
use Mitra\Http\Message\ResponseFactoryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Client\ClientInterface;

final class HttpClientServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container['api_http_client'] = static function (Container $container): ClientInterface {
            return new Curl(
                $container[ResponseFactoryInterface::class],
                ['timeout' => 15] // Short time out as we don't want to block the user for too long
            );
        };
    }
}

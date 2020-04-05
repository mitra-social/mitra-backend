<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Buzz\Client\Curl;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Client\ClientInterface;
use Slim\Psr7\Factory\ResponseFactory as PsrResponseFactory;

final class HttpClientServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container['api_http_client'] = static function (Container $container): ClientInterface {
            return new Curl(
                $container[PsrResponseFactory::class],
                ['timeout' => 15] // Short time out as we don't want to block the user for too long
            );
        };
    }
}

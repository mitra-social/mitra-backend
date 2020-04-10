<?php

declare(strict_types=1);

namespace Integration;

use Mitra\ActivityPub\Client\ActivityPubClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class TestServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container[ActivityPubClient::class] = static function () {
            return null;
        };
    }
}

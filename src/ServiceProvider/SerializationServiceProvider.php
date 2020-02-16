<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Decode\DelegateDecoder;
use Mitra\Serialization\Decode\JsonDecoder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class SerializationServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {

        $container[DecoderInterface::class] = function () {
            $decoder = new DelegateDecoder();

            $decoder->addDecoder('application/json', new JsonDecoder());

            return $decoder;
        };
    }
}

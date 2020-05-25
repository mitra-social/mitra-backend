<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Normalization\Normalizer;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Decode\DelegateDecoder;
use Mitra\Serialization\Decode\JsonDecoder;
use Mitra\Serialization\Encode\DelegateEncoder;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Serialization\Encode\JsonEncoder;
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
        $container[NormalizerInterface::class] = static function (): NormalizerInterface {
            return new Normalizer();
        };

        $container[EncoderInterface::class] = static function (): EncoderInterface {
            $encoder = new DelegateEncoder();
            $encoder->addEncoder('application/json', new JsonEncoder(
                JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ));

            return $encoder;
        };

        $container[DecoderInterface::class] = static function (): DecoderInterface {
            $decoder = new DelegateDecoder();
            $decoder->addDecoder('application/json', new JsonDecoder());

            return $decoder;
        };
    }
}

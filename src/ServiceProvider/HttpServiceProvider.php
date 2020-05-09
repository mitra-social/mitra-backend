<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use HttpSignatures\Signer;
use HttpSignatures\Verifier;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Http\Message\ResponseFactory;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Http\Signature\HttpKeyStore;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory as PsrResponseFactory;
use Slim\Psr7\Factory\UriFactory;

final class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[ResponseFactoryInterface::class] = static function (Container $container): ResponseFactoryInterface {
            return new ResponseFactory(
                new PsrResponseFactory(),
                $container[NormalizerInterface::class],
                $container[EncoderInterface::class],
                $container[EntityToDtoMapper::class]
            );
        };

        $container[PsrResponseFactoryInterface::class] = static function (
            Container $container
        ): PsrResponseFactoryInterface {
            return $container[ResponseFactoryInterface::class];
        };

        $container[RequestFactoryInterface::class] = static function (): RequestFactoryInterface {
            return new RequestFactory();
        };

        $container[UriFactoryInterface::class] = static function (): UriFactoryInterface {
            return new UriFactory();
        };

        $container[Verifier::class] = static function (Container $container): Verifier {
            return new Verifier(new HttpKeyStore(
                $container['api_http_client'],
                $container[RequestFactoryInterface::class]
            ));
        };
    }
}

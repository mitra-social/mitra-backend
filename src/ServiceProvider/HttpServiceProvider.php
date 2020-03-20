<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Dto\EntityToDtoMapper;
use Mitra\Http\Message\ResponseFactory;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Psr7\Factory\ResponseFactory as PsrResponseFactory;

final class HttpServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[ResponseFactoryInterface::class] = function () use ($container): ResponseFactoryInterface {
            return new ResponseFactory(
                $container[PsrResponseFactory::class],
                $container[EncoderInterface::class],
                $container[EntityToDtoMapper::class]
            );
        };
    }
}

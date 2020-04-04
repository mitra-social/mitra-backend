<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use ActivityPhp\Server;
use ActivityPhp\Type\TypeResolver;
use ActivityPhp\TypeFactory;
use ActivityPhp\Server\Http\GuzzleActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\HttpSignature;
use Mitra\CommandBus\Handler\CreateUserCommandHandler;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ActivityPubServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[Server\Http\DecoderInterface::class] = function (): Server\Http\DecoderInterface {
            return new Server\Http\JsonDecoder();
        };

        $container[GuzzleActivityPubClient::class] = function () use ($container): GuzzleActivityPubClient {
            return new GuzzleActivityPubClient($container[Server\Http\DecoderInterface::class], 0.5);
        };

        $container[Server\Http\WebFingerClient::class] = function () use ($container): Server\Http\WebFingerClient {
            return new Server\Http\WebFingerClient($container[GuzzleActivityPubClient::class], false);
        };

        $container[Server::class] = function () use ($container): Server {
            $typeFactory = new TypeFactory(new TypeResolver());
            $normalizer = new Server\Http\Normalizer();
            $denoramlizer = new Server\Http\Denormalizer($typeFactory);
            $encoder = new Server\Http\JsonEncoder();

            $config = [];

            return new Server(
                $container[ResponseFactoryInterface::class],
                $container[GuzzleActivityPubClient::class],
                $container[Server\Http\WebFingerClient::class],
                $typeFactory,
                $normalizer,
                $denoramlizer,
                $encoder,
                $container[Server\Http\DecoderInterface::class],
                $config
            );
        };

        $container[ActivityPubClient::class] = static function (Container $container): ActivityPubClient {
            return new ActivityPubClient(
                $container['api_http_client'],
                $container[RequestFactoryInterface::class],
                $container[EncoderInterface::class],
                $container[DecoderInterface::class],
                $container[ActivityPubDtoPopulator::class],
                new HttpSignature()
            );
        };
    }

    private function registerHandlers(Container $container): void
    {
        $container[CreateUserCommandHandler::class] = function () use ($container): CreateUserCommandHandler {
            return new CreateUserCommandHandler($container['doctrine.orm.em']);
        };
    }
}

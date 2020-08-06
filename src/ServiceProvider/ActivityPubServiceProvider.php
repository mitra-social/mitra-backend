<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use ActivityPhp\Server;
use ActivityPhp\Type\TypeResolver;
use ActivityPhp\TypeFactory;
use ActivityPhp\Server\Http\GuzzleActivityPubClient;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\InternalUserRequestSignerFactory;
use Mitra\ActivityPub\InternalUserRequestSignerFactoryInterface;
use Mitra\ActivityPub\RequestSigner;
use Mitra\ActivityPub\RequestSignerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\InternalUser;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;

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

        $container[ActivityPubClientInterface::class] = static function (
            Container $container
        ): ActivityPubClientInterface {
            return new ActivityPubClient(
                $container['api_http_client'],
                $container[RequestFactoryInterface::class],
                $container[NormalizerInterface::class],
                $container[EncoderInterface::class],
                $container[DecoderInterface::class],
                $container[ActivityPubDtoPopulator::class],
                $container[LoggerInterface::class]
            );
        };

        $container[RemoteObjectResolver::class] = static function (Container $container): RemoteObjectResolver {
            return new RemoteObjectResolver(
                $container[ActivityPubClientInterface::class],
                new ArrayCachePool(),
                $container[HashGeneratorInterface::class],
                $container[RequestSignerInterface::class]
            );
        };

        $container[ExternalUserResolver::class] = static function (Container $container): ExternalUserResolver {
            return new ExternalUserResolver(
                $container[RemoteObjectResolver::class],
                $container[ExternalUserRepository::class]
            );
        };

        $container['instanceUser'] = static function (Container $container): InternalUser {
            $user = new InternalUser(
                '8d1a908c-5995-4f95-9745-aa377813ffaa',
                'instance user',
                'donotreply@instance.com'
            );
            $user->setActor(new Person($user));
            $user->setKeyPair($container['instance']['publicKey'], $container['instance']['privateKey']);

            return $user;
        };

        $container[RequestSignerInterface::class] = static function (
            Container $container
        ): RequestSignerInterface {
            return new RequestSigner(
                $container[UriGenerator::class],
                $container['instance']['privateKey'],
                $container[LoggerInterface::class],
                ['Host', 'Date', 'Accept']
            );
        };
    }
}

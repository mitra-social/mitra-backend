<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use ActivityPhp\Server;
use ActivityPhp\Type\TypeResolver;
use ActivityPhp\TypeFactory;
use ActivityPhp\Server\Http\GuzzleActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Factory\ActivityStreamContentFactory;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\IdGeneratorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;

final class FactoryServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[ActivityStreamContentFactoryInterface::class] = static function (
            Container $container
        ): ActivityStreamContentFactoryInterface {
            return new ActivityStreamContentFactory(
                $container[IdGeneratorInterface::class],
                $container[HashGeneratorInterface::class],
                $container[NormalizerInterface::class],
            );
        };
    }
}

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
                $container[UriGenerator::class]
            );
        };

        $container[ExternalUserResolver::class] = static function (Container $container): ExternalUserResolver {
            return new ExternalUserResolver(
                $container[RemoteObjectResolver::class],
                $container[ExternalUserRepository::class]
            );
        };

        $container['instanceUser'] = static function (): InternalUser {
            $privateKey = <<<KEY
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDl5IEulItbhiBH
uk3lPtSDjUuRU4KrxWL0cekf/gbjqtIbKLtjiNDec927wDe3tBRPqGmLhQheQr8G
ysejo2gM947EoBKT7ox1jb6t/T2dPwStG9oZKbC5OhrYnoJwj5Y+6SAY1h7JHoS+
b0iJq8BKJO+U+d140/GQNCG+He5ZFmeSF9MwV0WHeBJtOjZ4WymPnxtat8OwTe6M
oLebeeQsHKDbSuiOgkGkZ98FS7IifXjVDL3CJQhRoI8Ui9WAQyytxQrRh/kRXOR7
DjEfh5ncODd52FW7aWqjtghZhAWCy3TdtE00ZnsMhDVfFG/tPGUP+QT4tUB2ITMY
NhCmzihxAgMBAAECggEAewiZ4lX5ZxgXOowImEHR9j2uTa6F5mwTE4PLIiYPhdSr
8YERGKnmLsePyaWLrDMiE6esJNEjs17BYV8xDPCtBROQQsbwal9mqJsdi3xWd0t8
szCOvpzDSb5BE+FxKdCOtkiJtwERJM3CeTCRf6x9krDFz9MRplK3m1drH5ZhMyd4
x33ryWYP6LH5sE1pqS9/GLVpJubSN5YG+GHxl0skSE+50HjOMkiwz6syu2dWgn7v
+ka5TaJHrV/s1Bcil9xG/FoanCFtoSYVGWK9l8rDusVtm+dvOHKM6h2PnnNI9tfp
hmGJLzwCMMy2QlJGuslkQEAUyyycY5bR/CyYm+NhSQKBgQDzChwDRx7HeXQ3v9tj
JFsSwmZR+jY94Psf/+M632WKwtuPC7AUkFmp6Sq8xjY3+OZZnbQTxv40n97MNSVD
USjWsVnG4SDWNHRlZ2ZZFrpHC9ZMTUblHyPgVQyIGCufSwJSw52h/GbGwXU4dzNZ
7bieqpWiMvNSSRsLJfEGUMCiJwKBgQDyJusaYP73x42B/n8WMiPLAkQlCKDA7XdI
Tma7tvrDOCW94yWH19y/5tPWa+m2wj4aBnBb8Gkp+CZhuYyQWh2v6vs8TYG8JjST
7RqxBoHxxZYaSShMilUt1y0o35AyLRgAfu+18UOlbUTcPj/+GAm7BuyNiNw5WkSj
KIorXgI3pwKBgQCVaaejFDwF4dLi2x4iqx0aQUzqJOrny8JW/9dQQDqKvhSAsmzD
l6Kn5GKTvz9h1bC3c05bwkBRVd+Ap0OLSP/UTR+mNo0bYxATryeqqWBHgS1zpyZo
gWZq6Z5UpJdczJ5XB0+HYEZG9nP8DLwTEyQm1zQ6jRwtgCgSCHdOrKJgjQKBgGTM
4Jc1g01/qx9O4nZJ3u51/gnwgoJtF3do84j2jYJQUB2wfYID0KetnccnWr2yNAm/
XmxXMl+/JbMOez3n1W3SgkzC8ttwh/h//dltHRCYsHg2tejOuNCBPxJBphPNA63J
KV3ylbc6Oiz4WMkcFojdRAFS1GGneuT0TjfpRUEHAoGAUrziP8/0OG+iMGztBqqR
OASuU0ph+eTooLukP2mjre/mHSR4/k4F97yRsMiR0lC1zn9a4dAvIlULRNkhh5yj
8+m1Wjsdrj07n2usnVBvcH48DKwtYvdR2lCvh+RRu7+L33qIC39VvgmLkseCn6/g
RnGILYgxVo3dz1UpMP0J05k=
-----END PRIVATE KEY-----

KEY;

            $publicKey = <<<KEY
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5eSBLpSLW4YgR7pN5T7U
g41LkVOCq8Vi9HHpH/4G46rSGyi7Y4jQ3nPdu8A3t7QUT6hpi4UIXkK/BsrHo6No
DPeOxKASk+6MdY2+rf09nT8ErRvaGSmwuToa2J6CcI+WPukgGNYeyR6Evm9IiavA
SiTvlPndeNPxkDQhvh3uWRZnkhfTMFdFh3gSbTo2eFspj58bWrfDsE3ujKC3m3nk
LByg20rojoJBpGffBUuyIn141Qy9wiUIUaCPFIvVgEMsrcUK0Yf5EVzkew4xH4eZ
3Dg3edhVu2lqo7YIWYQFgst03bRNNGZ7DIQ1XxRv7TxlD/kE+LVAdiEzGDYQps4o
cQIDAQAB
-----END PUBLIC KEY-----

KEY;

            $user = new InternalUser(
                '8d1a908c-5995-4f95-9745-aa377813ffaa',
                'instance user',
                'donotreply@instance.com'
            );
            $user->setActor(new Person($user));
            $user->setKeyPair($publicKey, $privateKey);

            return $user;
        };
    }
}

<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Doctrine\ORM\EntityManagerInterface;
use HttpSignatures\Verifier;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Middleware\AcceptAndContentTypeMiddleware;
use Mitra\Middleware\LogErrorMiddleware;
use Mitra\Middleware\RequestCycleCleanupMiddleware;
use Mitra\Middleware\ValidateHttpSignatureMiddleware;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Tuupola\Middleware\JwtAuthentication;

final class MiddlewareServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[RequestCycleCleanupMiddleware::class] = static function (
            Container $container
        ): RequestCycleCleanupMiddleware {
            return new RequestCycleCleanupMiddleware(
                $container[EntityManagerInterface::class],
                $container[LoggerInterface::class]
            );
        };

        $container[AcceptAndContentTypeMiddleware::class] = static function (
            Container $container
        ): AcceptAndContentTypeMiddleware {
            return new AcceptAndContentTypeMiddleware($container[ResponseFactoryInterface::class]);
        };

        $container[JwtAuthentication::class] = static function (
            Container $container
        ): JwtAuthentication {
            return new JwtAuthentication([
                'path' => '/',
                'ignore' => [],
                'secret' => $container['jwt.secret'],
                'logger' => $container[LoggerInterface::class],
            ]);
        };

        $container[ValidateHttpSignatureMiddleware::class] = static function (
            Container $container
        ): ValidateHttpSignatureMiddleware {
            return new ValidateHttpSignatureMiddleware(
                $container[Verifier::class],
                $container[ResponseFactoryInterface::class],
                $container[LoggerInterface::class]
            );
        };
    }
}

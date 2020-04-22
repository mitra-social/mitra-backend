<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Slim\ErrorHandler\HttpErrorHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

final class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[HttpErrorHandler::class] = static function (Container $container): HttpErrorHandler {
            return new HttpErrorHandler(
                $container[ResponseFactoryInterface::class],
                $container[LoggerInterface::class]
            );
        };
    }
}

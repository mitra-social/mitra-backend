<?php

declare(strict_types=1);

namespace Mitra;

use Mitra\Middleware\AcceptAndContentTypeMiddleware;
use Mitra\Middleware\RequestCycleCleanupMiddleware;
use Mitra\Middleware\ValidateHttpSignatureMiddleware;
use Mitra\Routes\PrivateRouteProvider;
use Mitra\Routes\PublicRouterProvider;
use Mitra\ServiceProvider\ControllerServiceProvider;
use Mitra\ServiceProvider\ErrorHandlerServiceProvider;
use Mitra\ServiceProvider\MiddlewareServiceProvider;
use Mitra\ServiceProvider\SlimServiceProvider;
use Mitra\Slim\ErrorHandler\HttpErrorHandler;
use Pimple\Container;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\CallableResolver;
use Slim\Exception\HttpException;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;
use Tuupola\Middleware\JwtAuthentication;

final class AppFactory
{
    /**
     * @param Container $container
     * @return App
     */
    public function create(Container $container): App
    {
        $app = $this->createApp($container);

        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        $app->add(ValidateHttpSignatureMiddleware::class);
        $app->add(AcceptAndContentTypeMiddleware::class);
        $app->add(RequestCycleCleanupMiddleware::class);

        // Needs to be last middleware to handle all the errors
        $errorMiddleware = $app->addErrorMiddleware($container->get('debug'), true, true);
        $errorMiddleware->setErrorHandler(HttpException::class, HttpErrorHandler::class, true);

        $app->group('', new PublicRouterProvider());
        $app->group('', new PrivateRouteProvider())->add(JwtAuthentication::class);

        return $app;
    }

    /**
     * @param Container $container
     * @return App
     */
    private function createApp(Container $container): App
    {
        $container
            ->register(new SlimServiceProvider())
            ->register(new MiddlewareServiceProvider())
            ->register(new ControllerServiceProvider())
            ->register(new ErrorHandlerServiceProvider())
        ;

        return new App(
            $container[ResponseFactory::class],
            $container[ContainerInterface::class],
            $container[CallableResolver::class],
            $container[RouteCollector::class],
            $container[RouteResolverInterface::class]
        );
    }
}

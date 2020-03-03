<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\AppFactory;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Mitra\Tests\Helper\Constraint\ResponseStatusCodeConstraint;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;

abstract class IntegrationTestCase extends TestCase
{

    /**
     * @var App
     */
    protected static $app;

    /**
     * @var ServerRequestFactory
     */
    protected static $requestFactory;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @var UriFactory
     */
    protected static $uriFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $env = Env::immutable(
            new DelegateReader([new GetenvReader(), new EnvVarReader()]),
            new NullWriter(),
            new ArrayCachePool()
        );

        self::$app = (new AppFactory())->create($env);
        self::$container = self::$app->getContainer();
        self::$uriFactory = new UriFactory();
        self::$requestFactory = new ServerRequestFactory(null, self::$uriFactory);
    }

    protected function createRequest(
        string $method,
        string $path,
        string $content = null,
        array $headers = []
    ): ServerRequestInterface {
        $uri = self::$uriFactory->createUri($path)->withScheme('http')->withHost('localhost');
        $request = self::$requestFactory->createServerRequest($method, $uri);

        $request = $request->withHeader('Content-Type', 'application/json')->withHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (null !== $content) {
            $request->getBody()->write($content);
        }

        return $request;
    }

    protected function executeRequest(ServerRequestInterface $request): ResponseInterface
    {
        return self::$app->handle($request);
    }


    /**
     * @param integer           $expectedStatusCode
     * @param ResponseInterface $response
     * @return void
     */
    protected static function assertStatusCode(int $expectedStatusCode, ResponseInterface $response): void
    {
        self::assertThat($response, new ResponseStatusCodeConstraint($expectedStatusCode));
    }

    protected function getContainer(): ContainerInterface
    {
        return self::$container;
    }
}

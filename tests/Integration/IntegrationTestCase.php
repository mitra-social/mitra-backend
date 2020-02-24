<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Cache\Adapter\Void\VoidCachePool;
use Mitra\AppFactory;
use Mitra\Env\Env;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Mitra\Tests\Helper\Constraint\ResponseStatusCodeConstraint;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr6NullCache\Adapter\NullCacheItemPool;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $env = Env::mutable(new GetenvReader(), new NullWriter(), new VoidCachePool());

        self::$app = (new AppFactory())->create($env);
        self::$requestFactory = new ServerRequestFactory();
    }

    protected function createRequest(
        string $method,
        string $path,
        string $content,
        array $headers = []
    ): ServerRequestInterface {
        $request = self::$requestFactory->createServerRequest($method, $path);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        $request->getBody()->write($content);

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
    public static function assertStatusCode(int $expectedStatusCode, ResponseInterface $response): void
    {
        self::assertThat($response, new ResponseStatusCodeConstraint($expectedStatusCode));
    }
}

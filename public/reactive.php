<?php

declare(strict_types=1);

namespace Mitra;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Mitra\Logger\RequestContext;
use Mitra\React\ProcessManager\Process;
use Mitra\React\ProcessManager\ReactProcessManager;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as ReactSocketServer;
use React\EventLoop\Factory as ReactEventLoopFactory;

require __DIR__ . '/../vendor/autoload.php';

$env = Env::immutable(
    new DelegateReader([new GetenvReader(), new EnvVarReader()]),
    new NullWriter(),
    new ArrayCachePool()
);

$appEnv = $env->get('APP_ENV') ?? 'prod';
$port = $env->get('APP_PORT') ?? 8080;

$container = AppContainer::init($env);
$app = (new AppFactory())->create($container);

$loop = ReactEventLoopFactory::create();
$socket = new ReactSocketServer(sprintf('0.0.0.0:%s', $port), $loop);

$processManager = new ReactProcessManager(
    3,
    $loop,
    function () use ($socket) {
        $socket->resume();
    },
    function () use ($socket) {
        $socket->pause();
    }
);

$processManager->setProcessInterruptCallable(function (Process $process): void {
    printf(
        'Process %d finished running, processed %d requests' . PHP_EOL,
        $process->getPid(),
        $process['processedRequests']
    );
});

/** @var RequestContext $requestContext */
$requestContext = $app->getContainer()->get(RequestContext::class);

$server = new ReactHttpServer(
    function (ServerRequestInterface $request) use ($app, $requestContext, $processManager) {
        $processData = $processManager->getCurrentProcess();

        if (!isset($processData['processedRequests'])) {
            $processData['processedRequests'] = 0;
        }

        $processData['processedRequests'] += 1;

        $requestContext->setRequest($request);
        $response = $app->handle($request);

        $response = $response->withHeader('X-Process-Id', $processData->getPid());

        return new ReactResponse(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody()
        );
    }
);

$server->listen($socket);

echo sprintf("Server (%s) running at http://0.0.0.0:%s\n", $appEnv, $port);

$processManager->run();

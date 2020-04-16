<?php

namespace Mitra;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Mitra\Logger\RequestContext;
use Mitra\React\Process;
use Mitra\React\ProcessManager;
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

/** @var RequestContext $requestContext */
$requestContext = $app->getContainer()->get(RequestContext::class);

$processManager = new ProcessManager($socket, $loop, 3);

$processManager->setProcessInterruptCallable(function (Process $processData): void {
    fwrite(STDERR, sprintf(
        'Process %d finished running, processed %d requests' . PHP_EOL,
        $processData->getPid(),
        $processData['processedRequests']
    ));
});

$server = new ReactHttpServer(
    function (ServerRequestInterface $request) use ($app, $requestContext, $processManager) {
        $processData = $processManager->getCurrentProcess();

        if (!isset($processData['processedRequests'])) {
            $processData['processedRequests'] = 0;
        }

        $processData['processedRequests'] += 1;
        fwrite(STDERR, sprintf(
            'Processed request (pid: %d), processed %d requests' . PHP_EOL,
            $processData->getPid(),
            $processData['processedRequests']
        ));

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

$processManager->run();

echo sprintf("Server (%s) running at http://0.0.0.0:%s\n", $appEnv, $port);

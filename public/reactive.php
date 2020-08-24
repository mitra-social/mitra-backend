<?php

namespace Mitra;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Mitra\Logger\RequestContext;
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

$data = (object)[
    'processed' => 0,
];

$loop = ReactEventLoopFactory::create();

/** @var RequestContext $requestContext */
$requestContext = $app->getContainer()->get(RequestContext::class);

$server = new ReactHttpServer(function (ServerRequestInterface $request) use ($app, $requestContext, $data) {
    $data->processed++;
    $requestContext->setRequest($request);

    $response = $app->handle($request);

    return new ReactResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody()
    );
});

$socket = new ReactSocketServer(sprintf('0.0.0.0:%s', $port), $loop);
$server->listen($socket);

$fork = function (callable $child) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        throw new \RuntimeException('Cant fork a process');
    } elseif ($pid > 0) {
        return $pid;
    } else {
        posix_setsid();
        $child();
        exit(0);
    }
};

$processes = [];

for ($i = 1; $i < 10; $i++) {
    $socket->pause();

    $processes[] = $fork(function () use ($socket, $loop, $data) {
        $socket->resume();
        // Terminate process if SIGINT received (see line 103)
        $loop->addSignal(SIGINT, function () use ($data, $loop) {
            fwrite(STDERR, sprintf(
                'Process %s finished running, processed %d requests' . PHP_EOL,
                posix_getpid(),
                $data->processed
            ));
            $loop->stop();
        });
        $loop->run();
    });
}

// Terminate all processes by sending an interupt signal to them
$terminateProcesses = function () use ($processes, $loop) {
    foreach ($processes as $pid) {
        posix_kill($pid, SIGINT);
        $status = 0;
        pcntl_waitpid($pid, $status);
    }

    $loop->stop();
};

// SIGUSR2 used by nodemon to reload (check SIGTERM and SIGINT as well)
$loop->addSignal(SIGUSR2, $terminateProcesses);
$loop->addSignal(SIGINT, $terminateProcesses);
$loop->addSignal(SIGTERM, $terminateProcesses);

echo sprintf("Server (%s) running at http://0.0.0.0:%s\n", $appEnv, $port);

$loop->run();

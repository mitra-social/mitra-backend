<?php

namespace Mitra;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
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

$app = (new AppFactory())->create($env);

$loop = ReactEventLoopFactory::create();

$server = new ReactHttpServer(function (ServerRequestInterface $request) use ($app) {
    echo 'Requested ' , $request->getMethod() , ' ' , (string) $request->getUri() , print_r($request->getHeaders(), true);

    $response = $app->handle($request);

    return new ReactResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody()
    );
});

$socket = new ReactSocketServer(sprintf('0.0.0.0:%s', $port), $loop);
$server->listen($socket);

echo sprintf("Server (%s) running at http://0.0.0.0:%s\n", $appEnv, $port);

$loop->run();

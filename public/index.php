<?php

namespace Mitra;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as ReactSocketServer;
use React\EventLoop\Factory as ReactEventLoopFactory;

require __DIR__ . '/../vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'dev';
$port = getenv('APP_PORT') ?: 8080;

$app = (new AppFactory())->create($env);

$loop = ReactEventLoopFactory::create();

$server = new ReactHttpServer(function (ServerRequestInterface $request) use ($app) {
    $response = $app->handle($request);

    return new ReactResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody()
    );
});

$socket = new ReactSocketServer(sprintf('0.0.0.0:%s', $port), $loop);
$server->listen($socket);

echo sprintf("Server running at http://0.0.0.0:%s\n", $port);

$loop->run();

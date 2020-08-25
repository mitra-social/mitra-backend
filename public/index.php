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
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

$env = Env::immutable(
    new DelegateReader([new GetenvReader(), new EnvVarReader()]),
    new NullWriter(),
    new ArrayCachePool()
);

// Init app
$container = AppContainer::init($env);
$app = (new AppFactory())->create($container);

// Create request object
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

/** @var RequestContext $requestContext */
$requestContext = $app->getContainer()->get(RequestContext::class);
$requestContext->setRequest($request);

// Handle request
$app->run($request);

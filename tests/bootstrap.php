<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->setPsr4('Mitra\\Tests\\', __DIR__);

echo sprintf('PHP version: %s', phpversion()) . PHP_EOL;

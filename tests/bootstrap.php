<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->setPsr4('Mitra\\Tests\\', __DIR__);

// we need to tell Symfony that the current console has exactly 120 chars line length
putenv('COLUMNS=120');

echo sprintf('PHP version: %s', phpversion()) . PHP_EOL;

// Delete test database
passthru('bin/console dbal:database:drop --if-exists --force ');

// Create test database
passthru('bin/console dbal:database:create');

// Migrate test database
echo 'Run migrations... ';
$returnCode = null;
passthru('bin/console migrations:migrate --no-interaction --all-or-nothing -q', $returnCode);

if (0 === $returnCode) {
    echo 'Done!' . PHP_EOL;
} else {
    echo 'FAILED!' . PHP_EOL;
    exit;
}

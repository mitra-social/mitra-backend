<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Slim\Interfaces\RouteCollectorProxyInterface;

interface RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void;
}

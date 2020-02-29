<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\Me\ProfileController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class PrivateRouteProvider implements RouteProviderInterface
{

    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group->get('/me', ProfileController::class);
    }
}
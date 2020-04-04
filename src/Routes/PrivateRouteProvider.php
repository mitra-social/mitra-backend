<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\User\InboxController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\User\OutboxController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class PrivateRouteProvider implements RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group->get('/me', ProfileController::class);
        $group->get('/user/{preferredUsername}/inbox', InboxController::class)->setName('user-inbox-read');
        $group->post('/user/{preferredUsername}/outbox', OutboxController::class)->setName('user-outbox-write');
        $group->get('/user/{preferredUsername}/outbox', OutboxController::class)->setName('user-outbox-read');
    }
}

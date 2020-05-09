<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\User\FollowingListController;
use Mitra\Controller\User\InboxReadController;
use Mitra\Controller\Me\ProfileController;
use Mitra\Controller\User\OutboxWriteController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class PrivateRouteProvider implements RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group->get('/me', ProfileController::class);
        $group->get('/user/{username}/inbox', InboxReadController::class)->setName('user-inbox-read');
        $group->post('/user/{username}/outbox', OutboxWriteController::class)->setName('user-outbox-write');
        $group->get('/user/{username}/outbox', OutboxWriteController::class)->setName('user-outbox-read');
        $group->get('/user/{username}/following', FollowingListController::class)->setName('user-following');
    }
}

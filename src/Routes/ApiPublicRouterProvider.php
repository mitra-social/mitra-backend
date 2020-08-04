<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\System\MediaController;
use Mitra\Controller\System\PingController;
use Mitra\Controller\System\SharedInboxWriteController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\ActivityReadController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Controller\User\InboxWriteController;
use Mitra\Controller\User\InstanceUserReadController;
use Mitra\Controller\User\UserReadController;
use Mitra\Controller\Webfinger\WebfingerController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class ApiPublicRouterProvider implements RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group
            ->get('/ping', PingController::class)
            ->setName('ping');
        $group
            ->post('/token', TokenController::class)
            ->setName('token');
        $group
            ->post('/inbox', SharedInboxWriteController::class)
            ->setName('shared-inbox-write');
        $group
            ->post('/user', CreateUserController::class)
            ->setName('user-create');
        $group
            ->get('/user/{username}', UserReadController::class)
            ->setName('user-read');
        $group
            ->get('/instance/user', InstanceUserReadController::class)
            ->setName('instance-user-read');
        $group
            ->post('/user/{username}/inbox', InboxWriteController::class)
            ->setName('user-inbox-write');
        $group
            ->post('/user/{username}/activity/{activityId}', ActivityReadController::class)
            ->setName('user-activity-read');
        $group
            ->get('/.well-known/webfinger', WebfingerController::class)
            ->setName('webfinger');
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\System\PingController;
use Mitra\Controller\System\TokenController;
use Mitra\Controller\User\CreateUserController;
use Mitra\Controller\User\ReadUserController;
use Mitra\Controller\Webfinger\WebfingerController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class PublicRouterProvider implements RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group->get('/ping', PingController::class)->setName('ping');
        $group->post('/token', TokenController::class)->setName('token');
        $group->post('/user', CreateUserController::class)->setName('user-create');
        $group->get('/user/{preferredUsername}', ReadUserController::class)->setName('user-read');
        $group->get('/.well-known/webfinger', WebfingerController::class)->setName('webfinger');
    }
}

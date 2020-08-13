<?php

declare(strict_types=1);

namespace Mitra\Routes;

use Mitra\Controller\System\MediaController;
use Slim\Interfaces\RouteCollectorProxyInterface;

final class MediaPublicRouteProvider implements RouteProviderInterface
{
    public function __invoke(RouteCollectorProxyInterface $group): void
    {
        $group->get('/media/{hash}', MediaController::class)->setName('media-read');
    }
}

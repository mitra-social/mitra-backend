<?php

declare(strict_types=1);

namespace Mitra\Controller\System;

use Mitra\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class PingController
{

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(235, 'OK')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0')
        ;

        $response->getBody()->write(date('Y-m-d\TH:i:sT'));

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Controller\System;

use Mitra\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(235, 'OK')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0')
            ->withHeader('Content-Type', 'text/plain')
        ;

        $data = [
            sprintf('host: %s', $request->getUri()->getHost()),
            sprintf('scheme: %s', $request->getUri()->getScheme()),
            sprintf('serverDate: %s', date('Y-m-d\TH:i:sT')),
        ];

        $response->getBody()->write(implode("\n", $data));

        return $response;
    }
}

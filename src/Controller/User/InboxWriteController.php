<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class InboxWriteController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Write request to inbox', [
            'request.body' => (string) $request->getBody(),
            'request.headers' => $request->getHeaders(),
        ]);

        return $this->responseFactory->createResponse(501);
    }
}

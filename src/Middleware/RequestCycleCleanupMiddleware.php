<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use Doctrine\Common\Persistence\ObjectManager;
use Mitra\Orm\EntityManagerDecorator;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestCycleCleanupMiddleware
{

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EntityManagerDecorator $entityManager, Logger $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->entityManager->restoreIfClosed();
        $this->entityManager->reconnectIfNotPinged();

        $response = $handler->handle($request);

        $this->entityManager->clear();
        $this->logger->reset();

        return $response;
    }
}

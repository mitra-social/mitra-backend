<?php

namespace Mitra\Middleware;

use Doctrine\Common\Persistence\ObjectManager;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestCycleCleanupMiddleware
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ObjectManager $objectManager
     * @param Logger        $logger
     */
    public function __construct(ObjectManager $objectManager, Logger $logger)
    {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);

        $this->objectManager->clear();
        $this->logger->reset();

        return $response;
    }
}

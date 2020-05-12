<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

use League\Tactician\Middleware;

final class TacticianEventMiddleware implements Middleware
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function execute(object $command, callable $next)
    {
        $returnValue = $next($command);

        $this->eventDispatcher->releaseEvents();

        return $returnValue;
    }
}

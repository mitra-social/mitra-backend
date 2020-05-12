<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;
use Mitra\CommandBus\Event\ActivityPub\ContentDeclinedEvent;
use Mitra\CommandBus\EventDispatcherInterface;

final class ValidateContentCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(ValidateContentCommand $command): void
    {
        if (true) {
            $this->eventDispatcher->raise(new ContentAcceptedEvent());
        } else {
            $this->eventDispatcher->raise(new ContentDeclinedEvent());
        }
    }
}

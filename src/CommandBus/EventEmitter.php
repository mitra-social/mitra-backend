<?php

declare(strict_types=1);

namespace Mitra\CommandBus;

final class EventEmitter implements EventEmitterInterface
{
    /**
     * @var array<object>
     */
    private $raisedEvents = [];

    /**
     * @var EventBusInterface
     */
    private $eventBus;

    public function raise(EventInterface $event): void
    {
        $this->eventBus->dispatch($event);
    }
}

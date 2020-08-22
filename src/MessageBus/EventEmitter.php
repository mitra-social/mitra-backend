<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

final class EventEmitter implements EventEmitterInterface
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

    public function __construct(EventBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function raise(EventInterface $event): void
    {
        $this->eventBus->dispatch($event);
    }
}

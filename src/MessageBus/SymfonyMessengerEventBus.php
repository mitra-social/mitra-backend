<?php

declare(strict_types=1);

namespace Mitra\MessageBus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class SymfonyMessengerEventBus implements EventBusInterface
{

    /**
     * @var MessageBusInterface
     */
    private $eventBus;

    public function __construct(MessageBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function dispatch(EventInterface $event): void
    {
        $this->eventBus->dispatch(
            (new Envelope($event))->with(new DispatchAfterCurrentBusStamp())
        );
    }
}

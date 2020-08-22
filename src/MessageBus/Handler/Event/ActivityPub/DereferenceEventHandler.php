<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Event\ActivityPub;

use Mitra\MessageBus\Command\ActivityPub\DereferenceCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\MessageBus\Event\ActivityPub\DereferenceEvent;

final class DereferenceEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(DereferenceEvent $event): void
    {
        if (false === $event->shouldDereferenceObjects()) {
            return;
        }

        $this->commandBus->handle(new DereferenceCommand(
            $event->getActivityStreamContentEntity(),
            $event->getActivityStreamDto(),
            $event->getActor(),
            $event->shouldDereferenceObjects(),
            $event->getMaxDereferenceDepth(),
            $event->getCurrentDereferenceDepth()
        ));
    }
}

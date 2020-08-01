<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\DereferenceCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\DereferenceEvent;

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
            $event->getMaxDereferenceDepth(),
            $event->getCurrentDereferenceDepth()
        ));
    }
}

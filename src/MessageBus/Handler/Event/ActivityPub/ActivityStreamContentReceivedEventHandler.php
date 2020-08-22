<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Event\ActivityPub;

use Mitra\MessageBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;

final class ActivityStreamContentReceivedEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(ActivityStreamContentReceivedEvent $event): void
    {
        $this->commandBus->handle(new AttributeActivityStreamContentCommand(
            $event->getActivityStreamContentEntity(),
            $event->getActivityStreamDto(),
            $event->getActor(),
            $event->shouldDereferenceObjects()
        ));
    }
}

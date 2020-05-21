<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentReceivedEvent;

final class ActivityStreamContentReceivedEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __invoke(ActivityStreamContentReceivedEvent $event): void
    {
        $dto = $event->getActivityStreamDto();
        $entity = $event->getActivityStreamContentEntity();

        $this->commandBus->handle(new AttributeActivityStreamContentCommand($entity, $dto));
    }
}

<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Event\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Command\ActivityPub\DereferenceCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;

final class ActivityStreamContentPersistedEventHandler
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(ActivityStreamContentPersistedEvent $event): void
    {
        $dto = $event->getActivityStreamDto();
        $entity = $event->getActivityStreamContentEntity();
        $actor = $event->getActor();
        $shouldDereferenceObjects = $event->shouldDereferenceObjects();

        if (null !== $actor) {
            $this->commandBus->handle(new AssignActivityStreamContentToActorCommand(
                $entity,
                $dto,
                $actor,
                $shouldDereferenceObjects
            ));
        } else {
            $this->commandBus->handle(new AssignActivityStreamContentToFollowersCommand(
                $entity,
                $dto,
                null,
                $shouldDereferenceObjects
            ));
        }

        $this->commandBus->handle(new DereferenceCommand($entity, $dto, $actor, $shouldDereferenceObjects, 2, 1));
    }
}

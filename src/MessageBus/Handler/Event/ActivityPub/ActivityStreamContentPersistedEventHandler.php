<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Event\ActivityPub;

use Mitra\MessageBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\MessageBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\MessageBus\Command\ActivityPub\DereferenceCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;

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

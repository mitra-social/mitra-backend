<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Entity\ActivityStreamContentAssignment;
use Ramsey\Uuid\Uuid;

final class AssignActivityStreamContentToActorCommandHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    public function __construct(EntityManagerInterface $entityManager, EventEmitterInterface $eventEmitter)
    {
        $this->eventEmitter = $eventEmitter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AssignActivityStreamContentToActorCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $actor = $command->getActor();

        $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $actor, $entity);
        $this->entityManager->persist($assignment);

        $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
    }
}

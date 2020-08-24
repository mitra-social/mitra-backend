<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\MessageBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
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

    /**
     * @var ActivityStreamContentAssignmentRepositoryInterface
     */
    private $activityStreamContentAssignmentRepository;

    public function __construct(EntityManagerInterface $entityManager, EventEmitterInterface $eventEmitter)
    {
        $this->eventEmitter = $eventEmitter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AssignActivityStreamContentToActorCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $actor = $command->getActor();

        if (null !== $this->activityStreamContentAssignmentRepository->findAssignment($actor, $entity)) {
            return;
        }

        $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $actor, $entity);

        $this->entityManager->persist($actor);
        $this->entityManager->persist($assignment);

        $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
    }
}

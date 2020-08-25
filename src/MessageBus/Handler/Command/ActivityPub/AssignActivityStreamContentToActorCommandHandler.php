<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\MessageBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Repository\InternalUserRepository;
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
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var ActivityStreamContentAssignmentRepositoryInterface
     */
    private $activityStreamContentAssignmentRepository;

    public function __construct(
        ActivityStreamContentAssignmentRepositoryInterface $activityStreamContentAssignmentRepository,
        InternalUserRepository $internalUserRepository,
        EntityManagerInterface $entityManager,
        EventEmitterInterface $eventEmitter
    ) {
        $this->activityStreamContentAssignmentRepository = $activityStreamContentAssignmentRepository;
        $this->eventEmitter = $eventEmitter;
        $this->entityManager = $entityManager;
        $this->internalUserRepository = $internalUserRepository;
    }

    public function __invoke(AssignActivityStreamContentToActorCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $actor = $command->getActor();

        if (null !== $this->activityStreamContentAssignmentRepository->findAssignment($actor, $entity)) {
            return;
        }

        $userId = $actor->getUser()->getId();

        if (null === $user = $this->internalUserRepository->findById($userId)) {
            throw new \RuntimeException(sprintf('Could not find internal user with id `%s`', $userId));
        }

        $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $user->getActor(), $entity);

        $this->entityManager->persist($assignment);

        $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
    }
}

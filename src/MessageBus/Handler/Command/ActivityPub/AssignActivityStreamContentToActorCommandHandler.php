<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\MessageBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Entity\ActivityStreamContentAssignment;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        EventEmitterInterface $eventEmitter,
        InternalUserRepository $internalUserRepository
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->entityManager = $entityManager;
        $this->internalUserRepository = $internalUserRepository;
    }

    public function __invoke(AssignActivityStreamContentToActorCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $actor = $command->getActor();

        $userId = $actor->getUser()->getId();

        if (null === $user = $this->internalUserRepository->findById($userId)) {
            throw new \RuntimeException(sprintf('Could not find internal user with id `%s`', $userId));
        }

        $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $user->getActor(), $entity);


        $this->entityManager->persist($assignment);

        $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
    }
}

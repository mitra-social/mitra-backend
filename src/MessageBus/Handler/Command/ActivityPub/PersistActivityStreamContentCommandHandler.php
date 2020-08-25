<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\MessageBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\MessageBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;

final class PersistActivityStreamContentCommandHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ActivityStreamContentRepositoryInterface
     */
    private $activityStreamContentRepository;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityStreamContentRepositoryInterface $activityStreamContentRepository,
        EventEmitterInterface $eventEmitter
    ) {
        $this->entityManager = $entityManager;
        $this->activityStreamContentRepository = $activityStreamContentRepository;
        $this->eventEmitter = $eventEmitter;
    }

    public function __invoke(PersistActivityStreamContentCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();

        // TODO how to ensure no other process has written the same record till flush after this command happens
        $existingEntity = $this->activityStreamContentRepository->getByExternalId($entity->getExternalId());

        if (null === $existingEntity) {
            $this->entityManager->persist($entity);
        } else {
            $entity = $existingEntity;
        }

        $dto = $command->getActivityStreamDto();
        $actor = $command->getActor();

        $this->eventEmitter->raise(new ActivityStreamContentPersistedEvent(
            $entity,
            $dto,
            $actor,
            $command->shouldDereferenceObjects()
        ));
    }
}

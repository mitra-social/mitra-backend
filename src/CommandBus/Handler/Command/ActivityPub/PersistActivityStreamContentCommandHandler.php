<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;
use Mitra\CommandBus\EventEmitterInterface;
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

        if (null !== $this->activityStreamContentRepository->getByExternalId($entity->getExternalId())) {
            return;
        }

        $this->entityManager->persist($entity);

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

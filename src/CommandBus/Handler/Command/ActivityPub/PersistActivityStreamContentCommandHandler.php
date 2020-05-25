<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\PersistActivityStreamContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentPersistedEvent;
use Mitra\CommandBus\EventEmitterInterface;

final class PersistActivityStreamContentCommandHandler
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
        $this->entityManager = $entityManager;
        $this->eventEmitter = $eventEmitter;
    }

    public function __invoke(PersistActivityStreamContentCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();

        $this->entityManager->persist($entity);

        $dto = $command->getActivityStreamDto();

        $this->eventEmitter->raise(new ActivityStreamContentPersistedEvent($entity, $dto));
    }
}

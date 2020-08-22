<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\ObjectIdDeterminer;
use Mitra\Dto\Response\ActivityStreams\Activity\DeleteDto;
use Mitra\MessageBus\Command\ActivityPub\DeleteActivityStreamContentCommand;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;

/**
 * Removes an ActivityStream content from the database if it exists
 */
final class DeleteActivityStreamContentCommandHandler
{
    /**
     * @var ActivityStreamContentRepositoryInterface
     */
    private $activityStreamContentRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectIdDeterminer
     */
    private $objectIdDeterminer;

    public function __construct(
        ActivityStreamContentRepositoryInterface $activityStreamContentRepository,
        EntityManagerInterface $entityManager,
        ObjectIdDeterminer $objectIdDeterminer
    ) {
        $this->activityStreamContentRepository = $activityStreamContentRepository;
        $this->entityManager = $entityManager;
        $this->objectIdDeterminer = $objectIdDeterminer;
    }

    public function __invoke(DeleteActivityStreamContentCommand $command): void
    {
        $objectDto = $command->getActivityStreamDto();

        if (false === $objectDto instanceof DeleteDto) {
            return;
        }

        /** @var DeleteDto $objectDto */

        if (null === $objectId = $this->objectIdDeterminer->getId($objectDto->object)) {
            return;
        }

        $existingContent = $this->activityStreamContentRepository->getByExternalId($objectId);

        if (null === $existingContent) {
            return;
        }

        $this->entityManager->remove($existingContent);
    }
}

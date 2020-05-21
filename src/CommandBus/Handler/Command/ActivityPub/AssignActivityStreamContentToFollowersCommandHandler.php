<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Repository\SubscriptionRepository;
use Ramsey\Uuid\Uuid;

final class AssignActivityStreamContentToFollowersCommandHandler
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    public function __invoke(AssignActivityStreamContentToFollowersCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $attributedToActor = $entity->getAttributedTo();

        if (null === $attributedToActor) {
            return;
        }

        $audience = $this->determineAssignmentList($command->getActivityStreamDto());

        foreach ($audience as $audienceActor) {
            $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $audienceActor, $entity);
            $this->entityManager->persist($assignment);

            $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
        }
    }

    /**
     * @param ObjectDto $dto
     * @return array<Actor>
     */
    private function determineAssignmentList(ObjectDto $dto): array
    {
        // TODO: determine which actors are on this server and return them
        $dto->to;
        $dto->cc;
        $dto->bcc;
        $dto->bto;
        $dto->audience;

        return [];
    }
}

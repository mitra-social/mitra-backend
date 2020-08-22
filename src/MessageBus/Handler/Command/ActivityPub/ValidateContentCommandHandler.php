<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Mitra\MessageBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\MessageBus\Event\ActivityPub\ContentAcceptedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Repository\SubscriptionRepository;

final class ValidateContentCommandHandler
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    public function __construct(
        EventEmitterInterface $eventEmitter,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function __invoke(ValidateContentCommand $command): void
    {
        $dto = $command->getActivityStreamDto();
        $entity = $command->getActivityStreamContentEntity();
        $actor = $command->getActor();

        if ($this->isValid($dto, $entity)) {
            $this->eventEmitter->raise(new ContentAcceptedEvent(
                $entity,
                $dto,
                $actor,
                $command->shouldDereferenceObjects()
            ));
        }
    }

    private function isValid(ObjectDto $dto, ActivityStreamContent $entity): bool
    {
        // Only accept activities
        if (!$dto instanceof ActivityDto) {
            return false;
        }

        if (null === $entity->getAttributedTo()) {
            return true;
        }

        // Only accept content from an user who is actually followed by any user on our server
        if (0 === $this->subscriptionRepository->getFollowerCountForActor($entity->getAttributedTo())) {
            return false;
        }

        return true;
    }
}

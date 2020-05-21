<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\ValidateContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Repository\SubscriptionRepository;

final class ValidateContentCommandHandler
{
    /**
     * @var EventEmitterInterface
     */
    private $eventDispatcher;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    public function __construct(
        EventEmitterInterface $eventDispatcher,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function __invoke(ValidateContentCommand $command): void
    {
        $dto = $command->getActivityStreamDto();
        $entity = $command->getActivityStreamContentEntity();

        if ($this->isValid($dto, $entity)) {
            $this->eventDispatcher->raise(new ContentAcceptedEvent($entity, $dto));
        }
    }

    private function isValid(ObjectDto $dto, ActivityStreamContent $entity): bool
    {
        // Only accept activities
        if (!$dto instanceof ActivityDto) {
            return false;
        }

        // Only accept content from an user who is actually followed by any user on our server
        if (0 === $this->subscriptionRepository->getFollowerCountForActor($entity->getAttributedTo())) {
            return false;
        }

        return true;
    }
}

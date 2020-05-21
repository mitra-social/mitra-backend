<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\ProcessActivityStreamContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ContentAcceptedEvent;
use Mitra\CommandBus\Event\ActivityPub\ContentDeclinedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Repository\SubscriptionRepository;

final class ProcessActivityStreamContentCommandHandler
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    public function __construct(
        EventEmitterInterface $eventEmitter,
        SubscriptionRepository $subscriptionRepository,
        ExternalUserResolver $externalUserResolver
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->externalUserResolver = $externalUserResolver;
    }

    public function __invoke(ProcessActivityStreamContentCommand $command): void
    {
        $dto = $command->getActivityStreamDto();
        $entity = $command->getActivityStreamContentEntity();

        if ($this->validate($command->getActivityStreamDto())) {
            $this->eventEmitter->raise(new ContentAcceptedEvent($entity, $dto));
        } else {
            $this->eventEmitter->raise(new ContentDeclinedEvent($entity, $dto));
        }
    }

    private function validate(ObjectDto $objectDto): bool
    {
        if (!$objectDto instanceof ActivityDto) {
            return false;
        }

        $user = $this->externalUserResolver->resolve($objectDto->actor);

        if (0 === $this->subscriptionRepository->getFollowerCountForActor($user->getActor())) {
            return false;
        }

        return true;
    }
}

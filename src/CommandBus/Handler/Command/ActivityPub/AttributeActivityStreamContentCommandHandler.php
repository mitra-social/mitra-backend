<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\AttributeActivityStreamContentCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAttributedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;

final class AttributeActivityStreamContentCommandHandler
{
    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    public function __construct(EventEmitterInterface $eventEmitter, ExternalUserResolver $externalUserResolver)
    {
        $this->eventEmitter = $eventEmitter;
        $this->externalUserResolver = $externalUserResolver;
    }

    public function __invoke(AttributeActivityStreamContentCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $dto = $command->getActivityStreamDto();

        if (!$dto instanceof ActivityDto) {
            return;
        }

        $user = $this->externalUserResolver->resolve($dto->actor);

        $entity->setAttributedTo($user->getActor());

        $this->eventEmitter->raise(new ActivityStreamContentAttributedEvent($entity, $dto, $command->getActor()));
    }
}

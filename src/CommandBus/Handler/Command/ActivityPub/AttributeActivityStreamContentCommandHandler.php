<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EventEmitterInterface $eventEmitter,
        ExternalUserResolver $externalUserResolver,
        EntityManagerInterface $entityManager
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->externalUserResolver = $externalUserResolver;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AttributeActivityStreamContentCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $dto = $command->getActivityStreamDto();

        if (!$dto instanceof ActivityDto) {
            return;
        }

        if (null !== $dto->actor) {
            $user = $this->externalUserResolver->resolve($dto->actor);
            $this->entityManager->persist($user);

            $entity->setAttributedTo($user->getActor());
        }

        $this->eventEmitter->raise(new ActivityStreamContentAttributedEvent(
            $entity,
            $dto,
            $command->getActor(),
            $command->shouldDereferenceObjects()
        ));
    }
}

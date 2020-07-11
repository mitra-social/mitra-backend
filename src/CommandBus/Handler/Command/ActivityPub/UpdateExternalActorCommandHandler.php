<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolverException;
use Mitra\CommandBus\Command\ActivityPub\UpdateExternalActorCommand;
use Mitra\CommandBus\Event\ActivityPub\ExternalUserUpdatedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Psr\Log\LoggerInterface;

final class UpdateExternalActorCommandHandler
{

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    public function __construct(
        EventEmitterInterface $eventEmitter,
        RemoteObjectResolver $remoteObjectResolver,
        ExternalUserResolver $externalUserResolver,
        LoggerInterface $logger
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->externalUserResolver = $externalUserResolver;
        $this->logger = $logger;
    }

    public function __invoke(UpdateExternalActorCommand $command): void
    {
        $dto = $command->getActivityStreamDto();

        if (!$dto instanceof ActivityDto) {
            $this->logger->info(sprintf(
                'Skip updating user as type `%s` is not an activity',
                $dto->type
            ));
            return;
        }

        try {
            $object = $this->remoteObjectResolver->resolve($dto->object);
        } catch (RemoteObjectResolverException $e) {
            $this->logger->info(sprintf(
                'Skip updating user as object `%s` could not be resolved: %s',
                is_string($dto->object) || $dto->object instanceof LinkDto ? (string) $dto->object : '<unknown>',
                $e->getMessage()
            ));
            return;
        }

        if (!$object instanceof ActorInterface) {
            $this->logger->info(sprintf(
                'Skip updating user as object type `%s` is not an actor type',
                $object->type
            ));
            return;
        }

        if (null === $resolvedActor = $this->externalUserResolver->resolve($object)) {
            $this->logger->info(sprintf(
                'Skip updating user as user with external id `%s` is unknown',
                $object->id
            ));
            return;
        }

        $resolvedActor->setOutbox($object->getOutbox());
        $resolvedActor->setInbox($object->getInbox());
        $resolvedActor->setPreferredUsername($object->getPreferredUsername());

        $resolvedActor->getActor()->setName($object->getName());

        $this->eventEmitter->raise(new ExternalUserUpdatedEvent($resolvedActor->getActor(), $object));
    }
}

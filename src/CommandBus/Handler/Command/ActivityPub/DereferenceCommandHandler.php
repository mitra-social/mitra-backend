<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolverException;
use Mitra\CommandBus\Command\ActivityPub\DereferenceCommand;
use Mitra\CommandBus\Event\ActivityPub\DereferenceEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\User\InternalUser;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;

/**
 * This handler dereferences `inReplyTo` and `object` properties of an activity,
 * stores them in the database and links them to the original activity where they're originating from.
 */
final class DereferenceCommandHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ActivityStreamContentFactoryInterface
     */
    private $activityStreamContentFactory;

    /**
     * @var ActivityStreamContentRepositoryInterface
     */
    private $activityStreamContentRepository;

    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var InternalUser
     */
    private $instanceUser;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityStreamContentFactoryInterface $activityStreamContentFactory,
        ActivityStreamContentRepositoryInterface $activityStreamContentRepository,
        RemoteObjectResolver $remoteObjectResolver,
        EventEmitterInterface $eventEmitter,
        InternalUser $instanceUser
    ) {
        $this->entityManager = $entityManager;
        $this->activityStreamContentFactory = $activityStreamContentFactory;
        $this->activityStreamContentRepository = $activityStreamContentRepository;
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->eventEmitter = $eventEmitter;
        $this->instanceUser = $instanceUser;
    }

    public function __invoke(DereferenceCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $dto = $command->getActivityStreamDto();

        $this->dereferenceObjects($entity, $dto->inReplyTo, $command);

        if ($dto instanceof ActivityDto) {
            $this->dereferenceObjects($entity, $dto->object, $command);
        }
    }

    /**
     * @param ActivityStreamContent $entity
     * @param null|LinkDto|ObjectDto|string|array<LinkDto|ObjectDto|string> $objects
     * @param DereferenceCommand $command
     * @throws RemoteObjectResolverException
     */
    private function dereferenceObjects(
        ActivityStreamContent $entity,
        $objects,
        DereferenceCommand $command
    ): void {
        if (!$command->shouldDereferenceObjects()) {
            return;
        }
        
        $nextDereferenceDepth = $command->getCurrentDereferenceDepth() + 1;
        $emitDereferenceEvents = $command->getCurrentDereferenceDepth() <= $command->getMaxDereferenceDepth();
        $objects = is_array($objects) ? $objects : [$objects];

        $commandActor = $command->getActor();

        /** @var InternalUser|null $userContext */
        $userContext = $commandActor instanceof InternalUser ? $commandActor->getUser() : $this->instanceUser;

        foreach ($objects as $object) {
            $objectDto = null;

            if (is_string($object) || $object instanceof LinkDto) {
                $dereferencedObject = $this->activityStreamContentRepository->getByExternalId((string) $object);

                // Object is not yet in database
                if (null === $dereferencedObject) {
                    /** @var ObjectDto $objectDto */
                    $objectDto = $this->remoteObjectResolver->resolve($object, $userContext);

                    $dereferencedObject = $this->activityStreamContentFactory->createFromDto($objectDto);
                    $this->entityManager->persist($dereferencedObject);

                }

                $entity->addLinkedObject($dereferencedObject);
            } elseif ($object instanceof ObjectDto) {
                $objectDto = $object;
                $dereferencedObject = $command->getActivityStreamContentEntity();
            } else {
                continue;
            }

            if ($emitDereferenceEvents && null !== $objectDto) {
                $this->eventEmitter->raise(new DereferenceEvent(
                    $dereferencedObject,
                    $objectDto,
                    null,
                    $command->shouldDereferenceObjects(),
                    $command->getMaxDereferenceDepth(),
                    $nextDereferenceDepth
                ));
            }
        }
    }
}

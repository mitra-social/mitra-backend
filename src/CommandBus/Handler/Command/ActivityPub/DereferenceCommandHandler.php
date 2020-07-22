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

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityStreamContentFactoryInterface $activityStreamContentFactory,
        ActivityStreamContentRepositoryInterface $activityStreamContentRepository,
        RemoteObjectResolver $remoteObjectResolver,
        EventEmitterInterface $eventEmitter
    ) {
        $this->entityManager = $entityManager;
        $this->activityStreamContentFactory = $activityStreamContentFactory;
        $this->activityStreamContentRepository = $activityStreamContentRepository;
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->eventEmitter = $eventEmitter;
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
        $nextDereferenceDepth = $command->getCurrentDereferenceDepth() + 1;
        $emitDereferenceEvents = $command->getCurrentDereferenceDepth() <= $command->getMaxDereferenceDepth();
        $objects = is_array($objects) ? $objects : [$objects];
        /** @var InternalUser|null $userContext */
        $userContext =  $command->getActor() instanceof InternalUser ? $command->getActor()->getUser() : null;

        foreach ($objects as $object) {
            if (!is_string($object) && !$object instanceof LinkDto) {
                continue;
            }

            /** @var ObjectDto $objectDto */
            $objectDto = $this->remoteObjectResolver->resolve($object, $userContext);

            $dereferencedObject = $this->activityStreamContentRepository->getByExternalId($objectDto->id);

            // Object is not yet in database
            if (null === $dereferencedObject) {
                $dereferencedObject = $this->activityStreamContentFactory->createFromDto($objectDto);
                $this->entityManager->persist($dereferencedObject);

                if ($emitDereferenceEvents) {
                    $this->eventEmitter->raise(new DereferenceEvent(
                        $dereferencedObject,
                        $objectDto,
                        null,
                        $command->getMaxDereferenceDepth(),
                        $nextDereferenceDepth
                    ));
                }
            }

            $entity->addLinkedObject($dereferencedObject);
        }
    }
}

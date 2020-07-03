<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\HashGeneratorInterface;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\ActivityPub\Resolver\RemoteObjectResolverException;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToActorCommand;
use Mitra\CommandBus\Command\ActivityPub\DereferenceObjectCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDto;
use Mitra\Dto\Response\ActivityStreams\Activity\ActivityDtoInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Factory\ActivityStreamContentFactoryInterface;
use Mitra\Slim\IdGeneratorInterface;
use Ramsey\Uuid\Uuid;

final class DereferenceObjectCommandHandler
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
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityStreamContentFactoryInterface $activityStreamContentFactory,
        RemoteObjectResolver $remoteObjectResolver
    ) {
        $this->entityManager = $entityManager;
        $this->activityStreamContentFactory = $activityStreamContentFactory;
        $this->remoteObjectResolver = $remoteObjectResolver;
    }

    public function __invoke(DereferenceObjectCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $dto = $command->getActivityStreamDto();

        if (!$dto instanceof ActivityDto) {
            return;
        }

        $objects = is_array($dto->object) ? $dto->object : [$dto->object];

        foreach ($objects as $object) {
            if (!$this->isReference($object)) {
                continue;
            }

            $dereferencedObject = $this->getDereferenceObject($object);
            $this->entityManager->persist($dereferencedObject);
            $entity->addLinkedObject($dereferencedObject);
        }
    }

    private function isReference($object): bool
    {
        return is_string($object) || $object instanceof LinkDto;
    }

    /**
     * @param LinkDto|string $object
     * @return ActivityStreamContent
     * @throws RemoteObjectResolverException
     */
    private function getDereferenceObject($object): ActivityStreamContent
    {
        /** @var ObjectDto $objectDto */
        $objectDto = $this->remoteObjectResolver->resolve($object);

        return $this->activityStreamContentFactory->createFromDto($objectDto);
    }
}

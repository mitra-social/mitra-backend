<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\RemoteObjectResolver;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ExternalUserRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class FollowCommandHandler
{
    /**
     * @var ExternalUserRepository
     */
    private $externalUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    public function __construct(
        ExternalUserRepository $externalUserRepository,
        EntityManagerInterface $entityManager,
        RemoteObjectResolver $remoteObjectResolver,
        LoggerInterface $logger
    ) {
        $this->externalUserRepository = $externalUserRepository;
        $this->entityManager = $entityManager;
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->logger = $logger;
    }

    public function __invoke(FollowCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $follow = $command->getFollowDto();

        if (null === $objectExternalUser = $this->getExternalUser($follow->object)) {
            throw new \RuntimeException('Could not resolve `$object`');
        }

        $this->logger->info('Persist subscription to database! Wuhuu!');

        $subscription = new Subscription(
            Uuid::uuid4()->toString(),
            $command->getActor(),
            $objectExternalUser->getActor(),
            new \DateTime()
        );

        $this->entityManager->persist($objectExternalUser);
        $this->entityManager->persist($subscription);
    }

    /**
     * @param string|ObjectDto|LinkDto $object
     * @return null|ExternalUser
     * @throws \Mitra\ActivityPub\Resolver\RemoteObjectResolverException
     */
    private function getExternalUser($object): ?ExternalUser
    {
        Assert::notNull($object);

        $externalId = null;

        if (is_string($object)) {
            $externalId = $object;
        } elseif ($object instanceof LinkDto) {
            $externalId = $object->href;
        } elseif ($object instanceof ObjectDto) {
            $externalId = $object->id;
        }

        if (null !== $externalUser = $this->externalUserRepository->findOneByExternalId($externalId)) {
            return $externalUser;
        }

        if (null === $resolvedObject = $this->remoteObjectResolver->resolve($object)) {
            return null;
        }

        Assert::isInstanceOf($resolvedObject, ActorInterface::class);

        /** @var ActorInterface $resolvedObject */

        $externalUser = new ExternalUser(
            Uuid::uuid4()->toString(),
            $resolvedObject->getId(),
            hash('sha256', $resolvedObject->getId()),
            $resolvedObject->getPreferredUsername(),
            $resolvedObject->getInbox(),
            $resolvedObject->getOutbox()
        );

        $actor = new Actor($externalUser);
        $actor->setName($resolvedObject->getName());
        //$actor->setIcon($resolvedObject->getIcon()); could be array... which one to choose then?

        $externalUser->setActor($actor);

        return $externalUser;
    }
}

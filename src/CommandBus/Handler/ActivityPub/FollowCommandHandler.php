<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\InternalUser;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class FollowCommandHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ExternalUserResolver
     */
    private $externalUserResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExternalUserResolver $externalUserResolver
    ) {
        $this->entityManager = $entityManager;
        $this->externalUserResolver = $externalUserResolver;
    }

    public function __invoke(FollowCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $follow = $command->getFollowDto();

        if (null === $objectExternalUser = $this->externalUserResolver->resolve($follow->object)) {
            throw new \RuntimeException('Could not resolve `$object`');
        }

        $subscription = new Subscription(
            Uuid::uuid4()->toString(),
            $command->getActor(),
            $objectExternalUser->getActor(),
            new \DateTime()
        );

        $this->entityManager->persist($objectExternalUser);
        $this->entityManager->persist($subscription);
    }
}

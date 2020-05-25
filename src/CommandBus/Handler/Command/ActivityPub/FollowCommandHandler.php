<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\SubscriptionRepository;
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

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExternalUserResolver $externalUserResolver,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->externalUserResolver = $externalUserResolver;
        $this->subscriptionRepository = $subscriptionRepository;
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

        $externalActor = $objectExternalUser->getActor();

        if (null !== $this->subscriptionRepository->getByActors($commandActor, $externalActor)) {
            return;
        }

        $subscription = new Subscription(
            Uuid::uuid4()->toString(),
            $commandActor,
            $externalActor,
            new \DateTime()
        );

        $this->entityManager->persist($objectExternalUser);
        $this->entityManager->persist($subscription);
    }
}

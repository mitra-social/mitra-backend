<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\MessageBus\Command\ActivityPub\FollowCommand;
use Mitra\MessageBus\Event\ActivityPub\ExternalUserUpdatedEvent;
use Mitra\MessageBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
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

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExternalUserResolver $externalUserResolver,
        SubscriptionRepository $subscriptionRepository,
        EventEmitterInterface $eventEmitter
    ) {
        $this->entityManager = $entityManager;
        $this->externalUserResolver = $externalUserResolver;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->eventEmitter = $eventEmitter;
    }

    public function __invoke(FollowCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $follow = $command->getFollowDto();

        $followObjects = is_array($follow->object) ? $follow->object : [$follow->object];

        foreach ($followObjects as $followObject) {
            $objectExternalUser = $this->externalUserResolver->resolve(
                $followObject,
                function (ExternalUser $externalUser, ActorInterface $resolvedActorDto): void {
                    $this->eventEmitter->raise(new ExternalUserUpdatedEvent(
                        $externalUser->getActor(),
                        $resolvedActorDto
                    ));
                }
            );

            if (null === $objectExternalUser) {
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
}

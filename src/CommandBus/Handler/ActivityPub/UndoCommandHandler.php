<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Resolver\ExternalUserResolver;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\SubscriptionRepository;
use Webmozart\Assert\Assert;

final class UndoCommandHandler
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
        ExternalUserResolver $externalUserResolver,
        EntityManagerInterface $entityManager,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->externalUserResolver = $externalUserResolver;
        $this->entityManager = $entityManager;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function __invoke(UndoCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $undo = $command->getUndoDto();

        if ($undo->object instanceof FollowDto) {
            $followObject = $undo->object;

            if (null === $objectExternalUser = $this->externalUserResolver->resolve($followObject->object)) {
                throw new \RuntimeException('Could not resolve `$object`');
            }

            $subscription = $this->subscriptionRepository->findByActors($commandActor, $objectExternalUser->getActor());

            if (null === $subscription) {
                return;
            }

            $this->entityManager->remove($subscription);
        }
    }
}

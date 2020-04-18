<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;

final class SubscriptionRepository
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function findByActors(Actor $subscribingActor, Actor $subscribedActor): ?Subscription
    {
        $qb = $this->entityRepository->createQueryBuilder('s');
        $qb
            ->where('s.subscribingActor = :subscribingActorId')
            ->andWhere('s.subscribedActor = :subscribedActorId')
            ->setParameter('subscribingActorId', $subscribingActor->getUser()->getId())
            ->setParameter('subscribedActorId', $subscribedActor->getUser()->getId());

        return $qb->getQuery()->getOneOrNullResult();
    }
}

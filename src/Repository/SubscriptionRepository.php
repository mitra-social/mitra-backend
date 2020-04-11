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
            ->where('s.subscribingActor = :subscribingActor')
            ->andWhere('s.subscribedActor = :subscribedActor')
            ->setParameter('subscribingActor', $subscribingActor)
            ->setParameter('subscribedActor', $subscribedActor);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

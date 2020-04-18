<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\ActivityStreamContentAssignment;
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
            ->setParameter('subscribingActorId', $subscribingActor->getUser())
            ->setParameter('subscribedActorId', $subscribedActor->getUser());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getFollowingCountForActor(Actor $actor)
    {
        $qb = $this->entityRepository->createQueryBuilder('s');
        $qb
            ->select($qb->expr()->count('s'))
            ->where('s.subscribingActor = :subscribingActor')
            ->setParameter('subscribingActor', $actor->getUser());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Actor $actor
     * @param int|null $offset
     * @param int|null $limit
     * @return array<Subscription>
     */
    public function findFollowingActorsForActor(Actor $actor, ?int $offset, ?int $limit): array
    {
        $qb = $this->entityRepository->createQueryBuilder('s')
            ->select('s', 'a')
            ->innerJoin('s.subscribedActor', 'a')
            ->where('s.subscribingActor = :actor')
            ->setParameters(['actor' => $actor]);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

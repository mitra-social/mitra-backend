<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;

final class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByActors(Actor $subscribingActor, Actor $subscribedActor): ?Subscription
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('s')
            ->from(Subscription::class, 's')
            ->where('s.subscribingActor = :subscribingActorId')
            ->andWhere('s.subscribedActor = :subscribedActorId')
            ->setParameter('subscribingActorId', $subscribingActor->getUser())
            ->setParameter('subscribedActorId', $subscribedActor->getUser());

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Returns the number of actors the given actor is following
     * @param Actor $actor
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFollowingCountForActor(Actor $actor): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Subscription::class, 's')
            ->select($qb->expr()->count('s'))
            ->where('s.subscribingActor = :subscribingActor')
            ->setParameter('subscribingActor', $actor->getUser());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns all actors the given actor is following
     * @param Actor $actor
     * @param int|null $offset
     * @param int|null $limit
     * @return array<Subscription>
     */
    public function getFollowingActorsForActor(Actor $actor, ?int $offset, ?int $limit): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->from(Subscription::class, 's')
            ->select('s', 'a')
            ->innerJoin('s.subscribedActor', 'a')
            ->where('s.subscribingActor = :actor')
            ->setParameter('actor', $actor);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all actors who follow the given actor
     * @param Actor $actor
     * @param int|null $offset
     * @param int|null $limit
     * @return array<Subscription>
     */
    public function getFollowersOfActor(Actor $actor, ?int $offset, ?int $limit): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->from(Subscription::class, 's')
            ->select('s', 'a')
            ->innerJoin('s.subscribingActor', 'a')
            ->where('s.subscribedActor = :actor')
            ->setParameter('actor', $actor);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the number of actors following the given actor
     * @param Actor $actor
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFollowerCountForActor(Actor $actor): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Subscription::class, 's')
            ->select($qb->expr()->count('s'))
            ->where('s.subscribedActor = :subscribedActor')
            ->setParameter('subscribedActor', $actor->getUser());

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

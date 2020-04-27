<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;

final class ActivityStreamContentAssignmentRepository extends EntityRepository
{
    /**
     * @param Actor $actor
     * @param int $offset
     * @param int $limit
     * @return array<ActivityStreamContentAssignment>
     * @throws \Exception
     */
    public function findContentForActor(Actor $actor, ?int $offset, ?int $limit): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->select('ca', 'c')
            ->innerJoin('ca.content', 'c')
            ->where('ca.actor = :actor')
            ->orderBy('c.published', 'DESC')
            ->setParameters([
                'actor' => $actor,
            ])
        ;

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function getTotalContentForUserId(Actor $actor): int
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->select($qb->expr()->count('ca'))
            ->where('ca.actor = :actor')
            ->setParameter('actor', $actor);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

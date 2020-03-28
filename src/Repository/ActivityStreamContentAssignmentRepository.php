<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User;

final class ActivityStreamContentAssignmentRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param int $offset
     * @param int $limit
     * @return array<ActivityStreamContentAssignment>
     * @throws \Exception
     */
    public function findContentForUserId(User $user, ?int $offset, ?int $limit): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->select('ca', 'c')
            ->innerJoin('ca.content', 'c')
            ->where('ca.user = :user')
            ->orderBy('c.published', 'DESC')
            ->setParameters([
                'user' => $user,
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

    public function getTotalContentForUserId(User $user): int
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->select($qb->expr()->count('ca'))
            ->where('ca.user = :user')
            ->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

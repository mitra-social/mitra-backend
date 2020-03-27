<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User;

final class ActivityStreamContentAssignmentRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param int $offset
     * @param int $limit
     * @return array<ActivityStreamContentAssignment>
     */
    public function findContentForUserId(User $user, ?int $offset, ?int $limit): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->select('ca', 'c')
            ->leftJoin('ca.content', 'c')
            ->where('ca.user = :user')
            ->setParameter('user', $user);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}

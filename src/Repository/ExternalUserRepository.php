<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\User\ExternalUser;

final class ExternalUserRepository
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function findOneByExternalId(string $externalId): ?ExternalUser
    {
        $qb = $this->entityRepository->createQueryBuilder('u');
        $qb
            ->select('u', 'a')
            ->leftJoin('u.actor', 'a')
            ->where('u.externalId = :externalId')
            ->setParameter('externalId', $externalId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

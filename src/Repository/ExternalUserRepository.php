<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Entity\User\ExternalUser;

final class ExternalUserRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findOneByExternalId(string $externalId): ?ExternalUser
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('u', 'a')
            ->from(ExternalUser::class, 'u')
            ->leftJoin('u.actor', 'a')
            ->where('u.externalId = :externalId')
            ->setParameter('externalId', $externalId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

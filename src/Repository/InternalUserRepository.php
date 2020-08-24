<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Entity\User\InternalUser;
use Psr\Http\Message\ServerRequestInterface;

final class InternalUserRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByUsername(string $username): ?InternalUser
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('u', 'a')
            ->from(InternalUser::class, 'u')
            ->leftJoin('u.actor', 'a')
            ->where('u.username = :username')
            ->setParameter('username', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findById(string $userId): ?InternalUser
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('u', 'a')
            ->from(InternalUser::class, 'u')
            ->leftJoin('u.actor', 'a')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

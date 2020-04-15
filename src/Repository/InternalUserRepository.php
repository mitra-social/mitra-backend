<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\User\InternalUser;
use Psr\Http\Message\ServerRequestInterface;

final class InternalUserRepository
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function findByUsername(string $username): ?InternalUser
    {
        $qb = $this->entityRepository->createQueryBuilder('u');
        $qb
            ->select('u', 'a')
            ->leftJoin('u.actor', 'a')
            ->where('u.username = :username')
            ->setParameter('username', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findById(string $userId): ?InternalUser
    {
        $qb = $this->entityRepository->createQueryBuilder('u');
        $qb
            ->select('u', 'a')
            ->leftJoin('u.actor', 'a')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function resolveFromRequest(ServerRequestInterface $request): ?InternalUser
    {
        $decodedToken = $request->getAttribute('token');

        if (!is_array($decodedToken) || !array_key_exists('userId', $decodedToken)) {
            return null;
        }

        return $this->findById($decodedToken['userId']);
    }
}

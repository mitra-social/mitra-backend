<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Doctrine\ORM\EntityRepository;
use Mitra\Entity\User;

final class UserRepository extends EntityRepository
{
    public function findOneByPreferredUsername(string $preferredUsername): ?User
    {
        /** @var User|null $user */
        $user = $this->findOneBy(['preferredUsername' => $preferredUsername]);

        return $user;
    }
}

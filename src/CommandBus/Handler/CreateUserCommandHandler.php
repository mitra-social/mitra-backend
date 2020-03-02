<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\CreateUserCommand;

final class CreateUserCommandHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $user = $command->getUser();

        $hashedPassword = password_hash($user->getPlaintextPassword(), PASSWORD_DEFAULT);

        if (false === $hashedPassword) {
            throw new \RuntimeException('Hash the password failed');
        }

        $user->setHashedPassword($hashedPassword);
        $user->setPlaintextPassword(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

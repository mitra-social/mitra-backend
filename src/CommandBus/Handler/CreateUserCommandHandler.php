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

        $user->setHashedPassword(password_hash($user->getPlaintextPassword(), PASSWORD_DEFAULT));
        $user->setPlaintextPassword(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

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
        $this->entityManager->persist($command->getUser());
        $this->entityManager->flush();
    }
}

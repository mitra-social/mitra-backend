<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Clock\ClockInterface;
use Mitra\MessageBus\Command\UserCreateCommand;
use Mitra\Entity\User\InternalUser;
use Mitra\MessageBus\Command\UserUpdateCommand;
use Mitra\Security\PasswordHasherInterface;
use Webmozart\Assert\Assert;

final class UserUpdateCommandHandler
{
    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var PasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(ClockInterface $clock, PasswordHasherInterface $passwordHasher)
    {
        $this->clock = $clock;
        $this->passwordHasher = $passwordHasher;
    }

    public function __invoke(UserUpdateCommand $command): void
    {
        $user = $command->getUser();

        Assert::notNull($user->getActor());

        $user->setUpdatedAt($this->clock->now());

        if (null !== $user->getPlaintextPassword()) {
            $this->hashPassword($user);
        }
    }

    private function hashPassword(InternalUser $user): void
    {
        $user->setHashedPassword($this->passwordHasher->hash($user->getPlaintextPassword()));
        $user->setPlaintextPassword(null);
    }
}

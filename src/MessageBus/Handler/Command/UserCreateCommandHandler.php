<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Clock\ClockInterface;
use Mitra\MessageBus\Command\UserCreateCommand;
use Mitra\Entity\User\InternalUser;
use Mitra\Security\PasswordHasherInterface;
use Webmozart\Assert\Assert;

final class UserCreateCommandHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var PasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClockInterface $clock,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->clock = $clock;
        $this->passwordHasher = $passwordHasher;
    }

    public function __invoke(UserCreateCommand $command): void
    {
        $user = $command->getUser();

        Assert::notNull($user->getActor());

        $user->setCreatedAt($this->clock->now());

        $this->hashPassword($user);

        if (null === $user->getPrivateKey()) {
            $this->seedKeyPair($user);
        }

        $this->entityManager->persist($user);
    }

    private function hashPassword(InternalUser $user): void
    {
        $user->setHashedPassword($this->passwordHasher->hash($user->getPlaintextPassword()));
        $user->setPlaintextPassword(null);
    }

    private function seedKeyPair(InternalUser $user): void
    {
        // Create the keypair
        if (false === $res = openssl_pkey_new()) {
            throw new \RuntimeException(
                sprintf('Could not generate key pair for user `%s` (id: %s)', $user->getUsername(), $user->getId())
            );
        }

        // Get private key
        openssl_pkey_export($res, $privKey);

        // Get public key
        if (false === $pubKey = openssl_pkey_get_details($res)) {
            throw new \RuntimeException(
                sprintf('Could not receive public key for user `%s` (id: %s)', $user->getUsername(), $user->getId())
            );
        }

        $user->setKeyPair($pubKey['key'], $privKey);
        unset($privKey);
    }
}

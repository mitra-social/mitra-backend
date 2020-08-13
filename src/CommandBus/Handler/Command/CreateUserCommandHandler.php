<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\Entity\User\InternalUser;
use Webmozart\Assert\Assert;

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

        Assert::notNull($user->getActor());

        $user->setCreatedAt(new \DateTime());

        $this->hashPassword($user);

        if (null === $user->getPrivateKey()) {
            $this->seedKeyPair($user);
        }

        $this->entityManager->persist($user);
    }

    private function hashPassword(InternalUser $user): void
    {
        $hashedPassword = password_hash($user->getPlaintextPassword(), PASSWORD_DEFAULT);

        if (false === $hashedPassword) {
            throw new \RuntimeException('Hashing the password failed');
        }

        $user->setHashedPassword($hashedPassword);
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

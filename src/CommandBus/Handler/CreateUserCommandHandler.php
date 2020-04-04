<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler;

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
        $this->seedKeyPair($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
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
        $res = openssl_pkey_new();

        // Get private key
        openssl_pkey_export($res, $privKey);
        $user->setPrivateKey($privKey);
        unset($privKey);

        // Get public key
        $pubKey = openssl_pkey_get_details($res);
        $user->setPublicKey($pubKey['key']);
    }
}

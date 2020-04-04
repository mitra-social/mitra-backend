<?php

namespace Mitra\Tests\Integration;

use Firebase\JWT\JWT;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\InternalUser;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @method ContainerInterface getContainer()
 */
trait CreateUserTrait
{
    protected function createUser(?string $password = null): InternalUser
    {
        $userId = Uuid::uuid4()->toString();
        $username = 'john.doe.' . uniqid();
        $plaintextPassword = $password ?? 's0mePässw0rd';

        $user = new InternalUser($userId, $username, $username . '@example.com');
        $user->setPlaintextPassword($plaintextPassword);

        $keyPair = $this->generateKeyPair();

        $user->setPrivateKey($keyPair['private']);
        $user->setPublicKey($keyPair['public']);

        $actor = new Person($user);

        $user->setActor($actor);

        $this->getContainer()->get(CommandBusInterface::class)->handle(new CreateUserCommand($user));

        return $user;
    }

    protected function createTokenForUser(InternalUser $user): string
    {
        return JWT::encode(['userId' => $user->getId()], $this->getContainer()->get('jwt.secret'));
    }

    private function generateKeyPair(): array
    {
        // Create the keypair
        $res = openssl_pkey_new();

        // Get private key
        openssl_pkey_export($res, $privKey);

        // Get public key
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return ['public' => $pubKey, 'private' => $privKey];
    }
}

<?php

namespace Mitra\Tests\Integration;

use Firebase\JWT\JWT;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Entity\User;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @method ContainerInterface getContainer()
 */
trait CreateUserTrait
{
    protected function createUser(): User
    {
        $userId = Uuid::uuid4()->toString();
        $username = 'john.doe.' . uniqid();
        $plaintextPassword = 's0mePässw0rd';

        $user = new User($userId, $username, $username . '@example.com');
        $user->setPlaintextPassword($plaintextPassword);

        $this->getContainer()->get(CommandBusInterface::class)->handle(new CreateUserCommand($user));

        return $user;
    }

    protected function createTokenForUser(User $user): string
    {
        return JWT::encode(['userId' => $user->getId()], $this->getContainer()->get('jwt.secret'));
    }
}

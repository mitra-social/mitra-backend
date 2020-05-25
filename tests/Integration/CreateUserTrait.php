<?php

namespace Mitra\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * @method ContainerInterface getContainer()
 */
trait CreateUserTrait
{
    protected function createExternalUser(?string $preferredUsername = null, string $actorType = 'Person'): ExternalUser
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        if (null === $preferredUsername) {
            $preferredUsername = sprintf('bob.%s', uniqid());
        }

        $externalUserId = sprintf('https://example.com/user/%s', $preferredUsername);

        $externalUser = new ExternalUser(
            Uuid::uuid4()->toString(),
            $externalUserId,
            hash('sha256', $externalUserId),
            $preferredUsername,
            sprintf('https://example.com/user/%s/inbox', $preferredUsername),
            sprintf('https://example.com/user/%s/outbox', $preferredUsername)
        );

        if ('Person' === $actorType) {
            $actor = new Person($externalUser);
        } elseif ('Organization' === $actorType) {
            $actor = new Organization($externalUser);
        } else {
            throw new \RuntimeException(sprintf('Unsupported actor type `%s`', $actorType));
        }

        $actor->setName('Bob');

        $externalUser->setActor($actor);

        $entityManager->persist($externalUser);
        $entityManager->flush();

        return $externalUser;
    }

    protected function createInternalUser(?string $password = null): InternalUser
    {
        $userId = Uuid::uuid4()->toString();
        $username = 'john.doe.' . uniqid();
        $plaintextPassword = $password ?? 's0mePässw0rd';

        $user = new InternalUser($userId, $username, $username . '@example.com');
        $user->setPlaintextPassword($plaintextPassword);

        $actor = new Person($user);

        $user->setActor($actor);

        $this->getContainer()->get(CommandBusInterface::class)->handle(new CreateUserCommand($user));

        return $user;
    }

    protected function createTokenForUser(InternalUser $user): string
    {
        return JWT::encode(['userId' => $user->getId()], $this->getContainer()->get('jwt.secret'));
    }

    protected function signRequest(InternalUser $user, RequestInterface $request): RequestInterface
    {
        return (new Signer(
            new Key(sprintf('http://test.localhost/user/%s#main-key', $user->getUsername()), $user->getPrivateKey()),
            Algorithm::create('rsa-sha256'),
            new HeaderList(['(request-target)', 'Host', 'Accept'])
        ))->sign($request);
    }
}

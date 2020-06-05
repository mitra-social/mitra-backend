<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group Integration
 */
final class FollowingControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testReturnsForbiddenIfNotLoggedIn(): void
    {
        $user = $this->createInternalUser();

        $request = $this->createRequest('GET', sprintf('/user/%s/following', $user->getUsername()));
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsNotFoundForUnknownUser(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/following', 'not.found.username'), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }

    public function testReturnsForbiddenForDifferentUser(): void
    {
        $user1 = $this->createInternalUser();
        $user2 = $this->createInternalUser();
        $token = $this->createTokenForUser($user1);

        $request = $this->createRequest('GET', sprintf('/user/%s/following', $user2->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsFollowingActorsAsCollection(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/following', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Collection',
            'totalItems' => 0,
            'first' => sprintf('http://test.localhost/user/%s/following?page=0', $user->getUsername()),
            'last' => sprintf('http://test.localhost/user/%s/following?page=0', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsFollowingActorsAsCollectionPageWithParameterPage(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $externalId = 'http://example.com/user/paul';
        $externalUser = new ExternalUser(
            Uuid::uuid4()->toString(),
            $externalId,
            hash('sha256', $externalId),
            'paul',
            sprintf('%/inbox', $externalId),
            sprintf('%/outbox', $externalId),
        );
        $subscribedActor = new Person($externalUser);

        $subscription = new Subscription(
            Uuid::uuid4()->toString(),
            $user->getActor(),
            $subscribedActor,
            new \DateTimeImmutable()
        );

        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.em');

        $em->persist($externalUser);
        $em->persist($subscribedActor);
        $em->persist($subscription);
        $em->flush();
        $em->clear();

        $request = $this->createRequest('GET', sprintf('/user/%s/following?page=0', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'type' => 'CollectionPage',
            'totalItems' => 1,
            'items' => [
                [
                    'type' => 'Person',
                    'id' => $externalId,
                    'preferredUsername' => 'paul',
                    'inbox' => sprintf('%/inbox', $externalId),
                    'outbox' => sprintf('%/outbox', $externalId)
                ]
            ],
            'partOf' => sprintf('http://test.localhost/user/%s/following', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsNotFoundOnNotExistingPage(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/following?page=1', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }
}

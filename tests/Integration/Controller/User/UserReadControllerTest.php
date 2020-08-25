<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\User;

use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class UserReadControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testUserGetsReturnedSuccessfully(): void
    {
        $user = $this->createInternalUser('foo');

        $request = $this->createRequest('GET', sprintf('/user/%s', $user->getUsername()));

        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $responseData = json_decode((string) $response->getBody(), true);

        self::assertEquals(
            [
                '@context' => [
                    'https://www.w3.org/ns/activitystreams',
                    'https://w3id.org/security/v1',
                    [
                        'mitra' => 'https://mitra.social/#',
                        'internalUserId' => 'mitra:internalUserId',
                    ],
                ],
                'type' => 'Person',
                'internalUserId' => $user->getId(),
                'id' => sprintf('http://test.localhost/user/%s', $user->getUsername()),
                'published' => $user->getCreatedAt()->format('c'),
                'url' => sprintf('http://test.localhost/user/%s', $user->getUsername()),
                'preferredUsername' => $user->getUsername(),
                'inbox' => sprintf('http://test.localhost/user/%s/inbox', $user->getUsername()),
                'outbox' => sprintf('http://test.localhost/user/%s/outbox', $user->getUsername()),
                'following' => sprintf('http://test.localhost/user/%s/following', $user->getUsername()),
                'publicKey' => [
                    'id' => sprintf('http://test.localhost/user/%s#main-key', $user->getUsername()),
                    'owner' => sprintf('http://test.localhost/user/%s', $user->getUsername()),
                    'publicKeyPem' => $user->getPublicKey(),
                ],
            ],
            $responseData
        );
    }
}

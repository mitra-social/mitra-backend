<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\Me;

use Mitra\MessageBus\CommandBusInterface;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class ProfileControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = $this->getContainer()->get(CommandBusInterface::class);
    }

    public function testReturnsForbiddenIfNotLoggedIn(): void
    {
        $request = $this->createRequest('GET', '/me');
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsUserInformationIfAuthorized(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', '/me', null, ['Authorization' => sprintf('Bearer %s', $token)]);
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
                        'email' => 'mitra:email',
                    ],
                ],
                'type' => 'Person',
                'internalUserId' => $user->getId(),
                'email' => $user->getEmail(),
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

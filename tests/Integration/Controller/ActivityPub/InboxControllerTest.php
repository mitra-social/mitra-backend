<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\Me;

use Mitra\CommandBus\CommandBusInterface;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class InboxControllerTest extends IntegrationTestCase
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
        $user = $this->createUser();

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', $user->getUsername()));
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsNotFoundForUnknownUser(): void
    {
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', 'not.found.username'), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }

    public function testReturnsInboxAsOrderedCollection(): void
    {
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollection',
            'totalItems' => 0,
            'first' => sprintf('http://localhost/user/%s/inbox?page=0', $user->getUsername()),
            'last' => sprintf('http://localhost/user/%s/inbox?page=0', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsInboxAsOrderedCollectionPageWithParameterPage(): void
    {
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox?page=0', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollectionPage',
            'totalItems' => 0,
            'orderedItems' => [],
            'partOf' => sprintf('http://localhost/user/%s/inbox', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsNotFoundOnNotExistingPage(): void
    {
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox?page=1', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Tests\Integration\ClientMockTrait;
use Mitra\Tests\Integration\CreateContentTrait;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Slim\UriGenerator;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @group Integration
 */
final class InboxControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;
    use CreateContentTrait;
    use ClientMockTrait;

    /**
     * @var ResponseFactoryInterface
     */
    private static $responseFactory;

    /**
     * @var RequestFactoryInterface
     */
    private static $requestFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$responseFactory = self::$container->get(ResponseFactoryInterface::class);
        self::$requestFactory = self::$container->get(RequestFactoryInterface::class);
    }

    public function testReturnsForbiddenIfNotLoggedIn(): void
    {
        $user = $this->createInternalUser();

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', $user->getUsername()));
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsNotFoundForUnknownUser(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', 'not.found.username'), null, [
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

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox', $user2->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsInboxAsOrderedCollection(): void
    {
        $user = $this->createInternalUser();
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
            'first' => sprintf('http://test.localhost/user/%s/inbox?page=0', $user->getUsername()),
            'last' => sprintf('http://test.localhost/user/%s/inbox?page=0', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsInboxAsOrderedCollectionPageWithParameterPage(): void
    {
        $user = $this->createInternalUser();
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
            'partOf' => sprintf('http://test.localhost/user/%s/inbox', $user->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testReturnsNotFoundOnNotExistingPage(): void
    {
        $user = $this->createInternalUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox?page=1', $user->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }

    public function testReturnsInboxItemsWithBtoAndBccStripped(): void
    {
        //self::markTestIncomplete('todo');

        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);
        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->getContainer()->get(CommandBusInterface::class);

        $actorUsername = 'bob';
        $externalUser = $this->createExternalUser($actorUsername);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $bccUser = $this->createInternalUser();
        $btoUser = $this->createInternalUser();
        $token = $this->createTokenForUser($toUser);

        foreach ([$toUser, $bccUser, $btoUser] as $user) {
            $followDto = new FollowDto();
            $followDto->object = $externalUser->getExternalId();

            $commandBus->handle(new FollowCommand($user->getActor(), $followDto));
        }

        $dtoContent = 'Foo bar baz';
        
        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $actorUsername);
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];
        $dto->bto = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $btoUser->getUsername()]),
        ];
        $dto->bcc = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $bccUser->getUsername()]),
        ];

        $sampleContent = $this->createContent($dto);

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox?page=0', $toUser->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollectionPage',
            'totalItems' => 1,
            'orderedItems' => [
                [
                    'type' => 'Create',
                    'object' => [
                        'type' => 'Note',
                        'content' => $dtoContent,
                    ],
                    'actor' => 'https://example.com/user/bob',
                    'id' => $dto->id,
                    'to' => [
                        $toUserExternalId,
                    ],
                ],
            ],
            'partOf' => sprintf('http://test.localhost/user/%s/inbox', $toUser->getUsername()),
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }
}

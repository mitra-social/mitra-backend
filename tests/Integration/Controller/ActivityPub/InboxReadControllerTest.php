<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
use Mitra\Tests\Integration\CreateSubscriptionTrait;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Tests\Integration\CreateContentTrait;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Slim\UriGenerator;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @group Integration
 */
final class InboxReadControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;
    use CreateContentTrait;
    use CreateSubscriptionTrait;

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

    public function testReturnsInboxItemsWithBtoAndBccStrippedAndInlinedObjects(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds(['a46dfe3f-65dc-41db-a22b-3c3307601402']);

        $actorUsername = 'bob';
        $externalUser = $this->createExternalUser($actorUsername);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $bccUser = $this->createInternalUser();
        $btoUser = $this->createInternalUser();
        $token = $this->createTokenForUser($toUser);

        foreach ([$toUser, $bccUser, $btoUser] as $user) {
            /** @var InternalUser $user */
            $this->createSubscription($user->getActor(), $externalUser->getActor());
        }

        $inReplyToDto = new CreateDto();
        $inReplyToDto->id = sprintf('https://example.com/user/%s/post/98754', 'alice');
        $inReplyToDto->object = new NoteDto();
        $inReplyToDto->object->content = 'This is the initial note';

        $this->createContent($inReplyToDto, null);
        $dtoContent = 'This is the reply';
        
        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $actorUsername);
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->object->inReplyTo = $inReplyToDto->id;
        $dto->to = [
            $toUserExternalId,
        ];
        $dto->bto = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $btoUser->getUsername()]),
        ];
        $dto->bcc = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $bccUser->getUsername()]),
        ];

        $this->createContent($dto, $toUser->getActor());

        $request = $this->createRequest('GET', sprintf('/user/%s/inbox?page=0', $toUser->getUsername()), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
                [
                    'mitra' => 'https://mitra.social/#',
                    'registeredAt' => 'mitra:registeredAt',
                    'internalUserId' => 'mitra:internalUserId',
                ],
            ],
            'type' => 'OrderedCollectionPage',
            'totalItems' => 1,
            'orderedItems' => [
                [
                    'type' => 'Create',
                    'object' => [
                        'type' => 'Note',
                        'content' => $dtoContent,
                        'inReplyTo' => [
                            'type' => 'Create',
                            'object' => [
                                'type' => 'Note',
                                'content' => 'This is the initial note',
                            ],
                            'id' => $inReplyToDto->id,
                        ],
                    ],
                    'actor' => [
                        'id' => 'https://example.com/user/bob',
                        'preferredUsername' => 'bob',
                        'name' => 'Bob',
                        'type' => 'Person',
                        'inbox' => 'https://example.com/user/bob/inbox',
                        'outbox' => 'https://example.com/user/bob/outbox',
                        'internalUserId' => $externalUser->getId(),
                    ],
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

    public function testReturnsFilteredInboxItems(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $actorUsername1 = sprintf('bob.%s', uniqid());
        $actorUsername2 = sprintf('alice.%s', uniqid());
        $externalUser1 = $this->createExternalUser($actorUsername1);
        $externalUser2 = $this->createExternalUser($actorUsername2);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $token = $this->createTokenForUser($toUser);

        $this->createSubscription($toUser->getActor(), $externalUser1->getActor());
        $this->createSubscription($toUser->getActor(), $externalUser2->getActor());

        $dtoContent = 'Foo bar baz';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $actorUsername1);
        $dto->actor = $externalUser1->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];

        $this->createContent($dto, $toUser->getActor());

        $filteredDto = new CreateDto();
        $filteredDto->id = sprintf('https://example.com/user/%s/post/123456', $actorUsername2);
        $filteredDto->actor = $externalUser2->getExternalId();
        $filteredDto->object = new NoteDto();
        $filteredDto->object->content = 'This one should be filtered';
        $filteredDto->to = [
            $toUserExternalId,
        ];

        $this->createContent($filteredDto, $toUser->getActor());

        /** @var ActivityStreamContentAssignmentRepositoryInterface $repo */
        $repo = $this->getContainer()->get(ActivityStreamContentAssignmentRepositoryInterface::class);

        self::assertEquals(2, $repo->getTotalCountForActor($toUser->getActor(), null));

        $request = $this->createRequest('GET', sprintf(
            '/user/%s/inbox?page=0&filter=%s',
            $toUser->getUsername(),
            urlencode('attributedTo=' . $externalUser1->getId())
        ), null, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
                [
                    'mitra' => 'https://mitra.social/#',
                    'registeredAt' => 'mitra:registeredAt',
                    'internalUserId' => 'mitra:internalUserId',
                ],
            ],
            'type' => 'OrderedCollectionPage',
            'totalItems' => 1,
            'orderedItems' => [
                [
                    'type' => 'Create',
                    'object' => [
                        'type' => 'Note',
                        'content' => $dtoContent,
                    ],
                    'actor' => [
                        'id' => sprintf('https://example.com/user/%s', $actorUsername1),
                        'preferredUsername' => $actorUsername1,
                        'name' => 'Bob',
                        'type' => 'Person',
                        'inbox' => sprintf('https://example.com/user/%s/inbox', $actorUsername1),
                        'outbox' => sprintf('https://example.com/user/%s/outbox', $actorUsername1),
                        'internalUserId' => $externalUser1->getId(),
                    ],
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

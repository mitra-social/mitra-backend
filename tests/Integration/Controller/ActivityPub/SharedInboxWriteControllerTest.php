<?php

declare(strict_types=1);

namespace Integration\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UpdateDto;
use Mitra\Entity\Media;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Tests\Integration\CreateSubscriptionTrait;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Mitra\Tests\Integration\ClientMockTrait;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;

final class SharedInboxWriteControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;
    use CreateSubscriptionTrait;
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

    public function testProcessesIncomingContentSuccessfully(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $toUser = $this->createInternalUser();
        $ccUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $ccUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $ccUser->getUsername()]);
        $bccUser = $this->createInternalUser();
        $btoUser = $this->createInternalUser();
        $externalUser = $this->createExternalUser();

        foreach ([$toUser, $ccUser, $bccUser, $btoUser] as $user) {
            /** @var InternalUser $user */
            $this->createSubscription($user->getActor(), $externalUser->getActor());
        }

        $dtoContent = 'This is a note.';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];
        $dto->cc = [
            $ccUserExternalId,
        ];
        $dto->bto = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $btoUser->getUsername()]),
        ];
        $dto->bcc = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $bccUser->getUsername()]),
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        $request = $this->createRequest('POST', '/inbox', $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);

        foreach ([$toUser, $ccUser, $btoUser, $bccUser] as $user) {
            /** @var ActivityStreamContentAssignment[] $userContent */
            $userContent = $contentAssignmentRepository->findContentForActor($user->getActor(), null, null);

            self::assertCount(1, $userContent);
            self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
        }
    }

    public function testContentAssignmentSuccessfulForUserInSubCollection(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $toUser = $this->createInternalUser();
        $externalUser = $this->createExternalUser();

        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);

        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

        $dtoContent = 'This is a note.';

        $externalCollectionId = sprintf('https://example.com/user/%s/followers', $externalUser->getPreferredUsername());
        $externalCollectionFirstPage = $externalCollectionId . '?page=1';
        $unresolvableCollectionId = 'http://example.com/list/not.found';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            'https://www.w3.org/ns/activitystreams#Public',
            $externalCollectionId,
            $unresolvableCollectionId,
        ];

        $objectAndToResponse1 = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse1->getBody()->write(
            '{"type": "OrderedCollection", "totalItems": 1, "first": "' . $externalCollectionFirstPage . '"}'
        );

        $objectAndToResponse2 = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse2->getBody()->write(
            '{"type": "OrderedCollectionPage", "totalItems": 1, "orderedItems": ["' . $toUserExternalId . '"]}'
        );

        $objectAndToResponse3 = self::$responseFactory->createResponse(404);

        $requestResolveExternalCollection = self::$requestFactory->createRequest('GET', $externalCollectionId);
        $requestResolveExternalCollectionPage = self::$requestFactory->createRequest(
            'GET',
            $externalCollectionFirstPage
        );
        $requestResolveUnresolvableExternalCollection = self::$requestFactory->createRequest(
            'GET',
            $unresolvableCollectionId
        );

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestResolveExternalCollection,
                $objectAndToResponse1,
            ],
            [
                $requestResolveExternalCollectionPage,
                $objectAndToResponse2,
            ],
            [
                $requestResolveUnresolvableExternalCollection,
                $objectAndToResponse3,
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        $request = $this->createRequest('POST', '/inbox', $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);

        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
    }


    public function testDoNotHandleAlreadyHandledContentTwice(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $toUser = $this->createInternalUser();
        $ccUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $ccUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $ccUser->getUsername()]);
        $bccUser = $this->createInternalUser();
        $btoUser = $this->createInternalUser();
        $externalUser = $this->createExternalUser();

        foreach ([$toUser, $ccUser, $bccUser, $btoUser] as $user) {
            /** @var InternalUser $user */
            $this->createSubscription($user->getActor(), $externalUser->getActor());
        }

        $dtoContent = 'This is a note.';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];
        $dto->cc = [
            $ccUserExternalId,
        ];
        $dto->bto = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $btoUser->getUsername()]),
        ];
        $dto->bcc = [
            $uriGenerator->fullUrlFor('user-read', ['username' => $bccUser->getUsername()]),
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        $request = $this->createRequest('POST', '/inbox', $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);

        foreach ([$toUser, $ccUser, $btoUser, $bccUser] as $user) {
            /** @var ActivityStreamContentAssignment[] $userContent */
            $userContent = $contentAssignmentRepository->findContentForActor($user->getActor(), null, null);

            self::assertCount(1, $userContent);
            self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
        }

        $request = $this->createRequest('POST', '/inbox', $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);
    }

    public function testUpdateExternalActorOnUpdateContainingAnActorObject(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $externalUser = $this->createExternalUser();

        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

        $newPreferredUsername = 'robert.' . uniqid();

        $dto = new UpdateDto();
        $dto->id = sprintf('https://example.com/user/%s/update/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new PersonDto();
        $dto->object->id = $externalUser->getExternalId();
        $dto->object->name = $newPreferredUsername;
        $dto->object->icon = 'https://example.com/images/icon123.png';
        $dto->object->inbox = sprintf('https://example.com/user/%s/inbox', $newPreferredUsername);
        $dto->object->outbox = sprintf('https://example.com/user/%s/outbox', $newPreferredUsername);
        $dto->to = [
            $toUserExternalId,
        ];

        self::assertNull($externalUser->getActor()->getIcon());
        self::assertNotEquals($dto->object->name, $externalUser->getActor()->getName());
        self::assertNotEquals($dto->object->inbox, $externalUser->getInbox());
        self::assertNotEquals($dto->object->outbox, $externalUser->getOutbox());

        $iconData = 'pngIconDataHere';

        $requestIcon = self::$requestFactory->createRequest('GET', $dto->object->icon);
        $responseIcon = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'image/png');

        $responseIcon->getBody()->write($iconData);

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestIcon,
                $responseIcon,
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        $request = $this->createRequest('POST', '/inbox', $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);

        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);

        /** @var ExternalUserRepository $externalUserRepository */
        $externalUserRepository = $this->getContainer()->get(ExternalUserRepository::class);

        $updatedExternalUser = $externalUserRepository->findOneByExternalId($externalUser->getExternalId());

        self::assertEquals($dto->object->name, $updatedExternalUser->getActor()->getName());
        self::assertNotNull($updatedExternalUser->getActor()->getIcon());
        self::assertEquals($dto->object->icon, $updatedExternalUser->getActor()->getIcon()->getOriginalUri());
        self::assertEquals(
            sprintf('icons/%s.png', md5($iconData)),
            $updatedExternalUser->getActor()->getIcon()->getLocalUri()
        );
        self::assertEquals($dto->object->inbox, $updatedExternalUser->getInbox());
        self::assertEquals($dto->object->outbox, $updatedExternalUser->getOutbox());
    }
}

<?php

declare(strict_types=1);

namespace Integration\Controller\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\DeleteDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Repository\ActivityStreamContentAssignmentRepositoryInterface;
use Mitra\Repository\ActivityStreamContentRepositoryInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Slim\UriGeneratorInterface;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
use Mitra\Tests\Integration\CreateContentTrait;
use Mitra\Tests\Integration\CreateSubscriptionTrait;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Tests\Integration\ClientMockTrait;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;

final class InboxWriteControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;
    use CreateSubscriptionTrait;
    use ClientMockTrait;
    use CreateContentTrait;

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

    public function testReceiveSameContentTwiceForDifferentUsers(): void
    {
        /** @var UriGeneratorInterface $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGeneratorInterface::class);

        $toUser1 = $this->createInternalUser();
        $toUser1ExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser1->getUsername()]);

        $toUser2 = $this->createInternalUser();
        $toUser2ExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser2->getUsername()]);

        $externalUser = $this->createExternalUser();

        /** @var InternalUser $user */
        $this->createSubscription($toUser1->getActor(), $externalUser->getActor());

        $dtoContent = 'This is a note.';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUser1ExternalId,
            $toUser2ExternalId,
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([
            '9fd9494c-dc99-4efc-ab14-1b502ae75682',
            'fd6a1576-d012-41c7-8795-4d0dde25f5d3',
        ]);

        $request1 = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser1->getUsername()), $payload);
        $response1 = $this->executeRequest($request1);

        self::assertStatusCode(201, $response1);

        $request2 = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser2->getUsername()), $payload);
        $response2 = $this->executeRequest($request2);

        self::assertStatusCode(201, $response2);

        /** @var ActivityStreamContentAssignmentRepositoryInterface $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(
            ActivityStreamContentAssignmentRepositoryInterface::class
        );


        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser1->getActor(), null, null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
    }

    public function testProcessesIncomingContentSuccessfully(): void
    {
        /** @var UriGeneratorInterface $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGeneratorInterface::class);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $externalUser = $this->createExternalUser();

        /** @var InternalUser $user */
        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

        $dtoContent = 'This is a note.';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds(['c20bd993-5d6c-410e-8a9a-c27ca408b1b6']);

        $request = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser->getUsername()), $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepositoryInterface $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(
            ActivityStreamContentAssignmentRepositoryInterface::class
        );


        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
    }

    public function testDereferencesLinkedObjectSuccessfully(): void
    {
        /** @var UriGeneratorInterface $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGeneratorInterface::class);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $externalUser = $this->createExternalUser();

        /** @var InternalUser $user */
        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

        // InReplyTo
        $referencedInReplyToUuid = '8dc8dd73-d785-4810-855d-f32323b51f74';
        $referencedInReplyToId = sprintf(
            'https://example.com/user/%s/object/%s',
            $externalUser->getPreferredUsername(),
            $referencedInReplyToUuid
        );
        $referencedInReplyToContent = 'This is a replyTo object.';
        $referenceInReplyTo = new NoteDto();
        $referenceInReplyTo->id = $referencedInReplyToId;
        $referenceInReplyTo->content = $referencedInReplyToContent;

        // Object
        $referencedObjectUuid = '237beefe-7259-42eb-84c3-322c4a36ad31';
        $referencedObjectId = sprintf(
            'https://example.com/user/%s/object/%s',
            $externalUser->getPreferredUsername(),
            $referencedObjectUuid
        );
        $referencedObjectContent = 'This is a note.';
        $referenceObject = new NoteDto();
        $referenceObject->id = $referencedObjectId;
        $referenceObject->content = $referencedObjectContent;
        $referenceObject->inReplyTo = $referencedInReplyToId;

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalUser->getPreferredUsername());
        $dto->actor = $externalUser->getExternalId();
        $dto->object = $referencedObjectId;
        $dto->to = [
            $toUserExternalId,
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dto), 'application/json');
        $referencedObjectPayload = $encoder->encode($normalizer->normalize($referenceObject), 'application/json');
        $referencedInReplyToPayload = $encoder->encode($normalizer->normalize($referenceInReplyTo), 'application/json');

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([
            'afb95f77-fd0b-455c-98d9-4defb13ba650', // incoming content
            $referencedObjectUuid,
            $referencedInReplyToUuid,
        ]);

        $objectResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectResponse->getBody()->write($referencedObjectPayload);

        $objectRequest = self::$requestFactory->createRequest('GET', $referencedObjectId);

        $inReplyToResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $inReplyToResponse->getBody()->write($referencedInReplyToPayload);

        $inReplyToRequest = self::$requestFactory->createRequest('GET', $referencedInReplyToId);

        $apiHttpClientMock = $this->getClientMock([
            [
                $objectRequest,
                $objectResponse,
            ],
            [
                $inReplyToRequest,
                $inReplyToResponse,
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        $request = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser->getUsername()), $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepositoryInterface $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(
            ActivityStreamContentAssignmentRepositoryInterface::class
        );

        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);

        $linkedObjects = $userContent[0]->getContent()->getLinkedObjects();
        self::assertCount(1, $linkedObjects);

        /** @var ActivityStreamContent $linkedObject */
        $linkedObject = $linkedObjects[0];
        self::assertEquals($referencedObjectId, $linkedObject->getExternalId());

        self::assertCount(1, $linkedObject->getLinkedObjects());

        /** @var ActivityStreamContent $linkedObject2 */
        $linkedObject2 = $linkedObject->getLinkedObjects()[0];
        self::assertEquals($referencedInReplyToId, $linkedObject2->getExternalId());
    }

    public function testDeletesContentOnDeleteActivity(): void
    {
        /** @var UriGeneratorInterface $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGeneratorInterface::class);

        $externalActorUsername = sprintf('bob.%s', uniqid());
        $externalUser = $this->createExternalUser($externalActorUsername);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);

        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

        $dtoContent = 'Foo bar baz';

        $dto = new CreateDto();
        $dto->id = sprintf('https://example.com/user/%s/post/123456', $externalActorUsername);
        $dto->actor = $externalUser->getExternalId();
        $dto->object = new NoteDto();
        $dto->object->content = $dtoContent;
        $dto->to = [
            $toUserExternalId,
        ];

        $this->createContent($dto, $toUser->getActor());

        /** @var ActivityStreamContentRepositoryInterface $contentRepository */
        $contentRepository = $this->getContainer()->get(
            ActivityStreamContentRepositoryInterface::class
        );

        /** @var ActivityStreamContentAssignmentRepositoryInterface $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(
            ActivityStreamContentAssignmentRepositoryInterface::class
        );

        $content = $contentRepository->getByExternalId($dto->id);
        self::assertNotNull($content);

        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([
            'ed777377-8cdd-4f92-b618-e2d0c4e254da',
        ]);

        $dtoDelete = new DeleteDto();
        $dtoDelete->id = sprintf('%s#delete', $dto->id);
        $dtoDelete->actor = $externalUser->getExternalId();
        $dtoDelete->object = $dto->id;
        $dtoDelete->to = [
            $toUserExternalId,
        ];

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);
        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->getContainer()->get(NormalizerInterface::class);

        $payload = $encoder->encode($normalizer->normalize($dtoDelete), 'application/json');

        $request = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser->getUsername()), $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        $content = $contentRepository->getByExternalId($dto->id);
        self::assertNull($content);

        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null, null);

        self::assertCount(0, $userContent);
    }
}

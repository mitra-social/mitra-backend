<?php

declare(strict_types=1);

namespace Integration\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UpdateDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Media;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
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

final class InboxWriteControllerTest extends IntegrationTestCase
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

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);


        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
    }


    public function testDereferencesLinkedObjectSuccessfully(): void
    {
        /** @var UriGenerator $uriGenerator */
        $uriGenerator = $this->getContainer()->get(UriGenerator::class);

        $toUser = $this->createInternalUser();
        $toUserExternalId = $uriGenerator->fullUrlFor('user-read', ['username' => $toUser->getUsername()]);
        $externalUser = $this->createExternalUser();

        /** @var InternalUser $user */
        $this->createSubscription($toUser->getActor(), $externalUser->getActor());

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

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds(['afb95f77-fd0b-455c-98d9-4defb13ba650', $referencedObjectUuid]);

        $objectAndToResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse->getBody()->write($referencedObjectPayload);

        $requestResolveExternalActor = self::$requestFactory->createRequest('GET', $referencedObjectId);

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestResolveExternalActor,
                $objectAndToResponse,
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        $request = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser->getUsername()), $payload);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ActivityStreamContentAssignmentRepository $contentAssignmentRepository */
        $contentAssignmentRepository = $this->getContainer()->get(ActivityStreamContentAssignmentRepository::class);


        /** @var ActivityStreamContentAssignment[] $userContent */
        $userContent = $contentAssignmentRepository->findContentForActor($toUser->getActor(), null, null);

        self::assertCount(1, $userContent);
        self::assertEquals($userContent[0]->getContent()->getExternalId(), $dto->id);
        self::assertCount(1, $userContent[0]->getContent()->getLinkedObjects());

        /** @var ActivityStreamContent $linkedObject */
        $linkedObject = $userContent[0]->getContent()->getLinkedObjects()[0];
        self::assertEquals($referencedObjectId, $linkedObject->getExternalId());
    }
}

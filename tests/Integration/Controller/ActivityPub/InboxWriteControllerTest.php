<?php

declare(strict_types=1);

namespace Integration\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UpdateDto;
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

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setId('c20bd993-5d6c-410e-8a9a-c27ca408b1b6');

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
}

<?php

declare(strict_types=1);

namespace Integration\Controller\ActivityPub;

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

    /**
     * @var ResponseFactoryInterface
     */
    private static $responseFactory;

    /**
     * @var RequestFactoryInterface
     */
    private static $requestFactory;

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

        $request = $this->createRequest('POST', sprintf('/user/%s/inbox', $toUser->getUsername()), $payload);
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
}

<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\MessageBus\Command\ActivityPub\FollowCommand;
use Mitra\MessageBus\CommandBusInterface;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Entity\Media;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Repository\SubscriptionRepositoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\IdGeneratorInterface;
use Mitra\Tests\Helper\Generator\ReflectedIdGenerator;
use Mitra\Tests\Integration\ClientMockTrait;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @group Integration
 */
final class OutboxControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;
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

    public function testSuccessfulFollow(): void
    {
        $followingUser = $this->createInternalUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();
        $iconUri = 'http://example.com/image/123.jpg';
        $activityUuid = 'f9a132ac-fd72-4705-a0bf-2d2e5827fe68';

        $objectAndTo = new PersonDto();
        $objectAndTo->id = $externalUserId;
        $objectAndTo->inbox = $externalUserId . '/inbox';
        $objectAndTo->outbox = $externalUserId . '/outbox';
        $objectAndTo->icon = $iconUri;

        $objectAndToResponseBody = sprintf(
            '{"type": "Person", "id": "%s", "inbox": "%s", "outbox": "%s", "icon": "%s"}',
            $externalUserId,
            $objectAndTo->inbox,
            $objectAndTo->outbox,
            $iconUri
        );

        $objectAndToResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse->getBody()->write($objectAndToResponseBody);

        $requestResolveExternalActor = self::$requestFactory->createRequest('GET', $externalUserId);
        $requestSendFollowToRecipient = self::$requestFactory->createRequest('POST', $objectAndTo->inbox)
            ->withHeader('Host', 'example.com')
            ->withHeader('Accept', 'application/activity+json');
        $requestActorIcon = self::$requestFactory->createRequest('GET', $iconUri);

        $requestSendFollowToRecipient = $this->signRequest($followingUser, $requestSendFollowToRecipient);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([
            $activityUuid,
        ]);

        $requestSendFollowToRecipient->getBody()->write($encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Follow',
            'object' => $objectAndTo->id,
            'actor' => $actorId,
            'id' => sprintf(
                'http://test.localhost/user/%s/activity/%s',
                $followingUser->getUsername(),
                $activityUuid
            ),
            'to' => $objectAndTo->id,
        ], 'application/json'));

        $iconData = 'jpegIconDataHere';

        $responseIcon = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'image/jpeg');

        $responseIcon->getBody()->write($iconData);

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestResolveExternalActor,
                $objectAndToResponse,
            ],
            [
                $requestActorIcon,
                $responseIcon,
            ],
            [
                $requestSendFollowToRecipient,
                self::$responseFactory->createResponse(201),
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        $body = '{"@context": "https://www.w3.org/ns/activitystreams","type": "Follow", ' .
            '"to": "' . $externalUserId . '", "object": "' . $externalUserId . '"}';

        $token = $this->createTokenForUser($followingUser);

        $request = $this->createRequest('POST', sprintf('/user/%s/outbox', $followingUser->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ExternalUserRepository $externalUserRepository */
        $externalUserRepository = $this->getContainer()->get(ExternalUserRepository::class);

        $followedUser = $externalUserRepository->findOneByExternalId($externalUserId);

        self::assertNotNull($followedUser);
        self::assertInstanceOf(Media::class, $followedUser->getActor()->getIcon());
        self::assertEquals($iconUri, $followedUser->getActor()->getIcon()->getOriginalUri());
        self::assertEquals(
            sprintf('icons/%s.jpg', md5($iconData)),
            $followedUser->getActor()->getIcon()->getLocalUri()
        );

        /** @var SubscriptionRepositoryInterface $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepositoryInterface::class);

        $subscription = $subscriptionRepository->getByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNotNull($subscription);
    }

    public function testFollowingSameUserSecondTimeDoesNothing(): void
    {
        $followingUser = $this->createInternalUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();
        $activityUuid = 'f73b1760-767e-46e3-9924-50cdcfea6b88';

        $objectAndTo = new PersonDto();
        $objectAndTo->id = $externalUserId;
        $objectAndTo->inbox = $externalUserId . '/inbox';
        $objectAndTo->outbox = $externalUserId . '/outbox';

        $objectAndToResponseBody = sprintf(
            '{"type": "Person", "id": "%s", "inbox": "%s", "outbox": "%s"}',
            $externalUserId,
            $objectAndTo->inbox,
            $objectAndTo->outbox
        );

        $objectAndToResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse->getBody()->write($objectAndToResponseBody);

        $requestResolveExternalActor = self::$requestFactory->createRequest('GET', $externalUserId);
        $requestSendFollowToRecipient = self::$requestFactory->createRequest('POST', $objectAndTo->inbox)
            ->withHeader('Host', 'example.com')
            ->withHeader('Accept', 'application/activity+json');

        $requestSendFollowToRecipient = $this->signRequest($followingUser, $requestSendFollowToRecipient);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([
            $activityUuid,
            $activityUuid,
        ]);

        $requestSendFollowToRecipient->getBody()->write($encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Follow',
            'object' => $objectAndTo->id,
            'actor' => $actorId,
            'id' => sprintf(
                'http://test.localhost/user/%s/activity/%s',
                $followingUser->getUsername(),
                $activityUuid
            ),
            'to' => $objectAndTo->id,
        ], 'application/json'));

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestResolveExternalActor,
                $objectAndToResponse,
            ],
            [
                $requestSendFollowToRecipient,
                self::$responseFactory->createResponse(201),
            ],
            [
                $requestSendFollowToRecipient,
                self::$responseFactory->createResponse(201),
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        $body = '{"@context": "https://www.w3.org/ns/activitystreams","type": "Follow", ' .
            '"to": "' . $externalUserId . '", "object": "' . $externalUserId . '"}';

        $token = $this->createTokenForUser($followingUser);

        $request = $this->createRequest('POST', sprintf('/user/%s/outbox', $followingUser->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        // Follow same actor again
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var ExternalUserRepository $externalUserRepository */
        $externalUserRepository = $this->getContainer()->get(ExternalUserRepository::class);

        $followedUser = $externalUserRepository->findOneByExternalId($externalUserId);

        self::assertNotNull($followedUser);

        /** @var SubscriptionRepositoryInterface $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepositoryInterface::class);

        $subscription = $subscriptionRepository->getByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNotNull($subscription);
    }

    public function testSuccessfulUnfollow(): void
    {
        // Prepare
        $followingUser = $this->createInternalUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();

        $objectAndTo = new PersonDto();
        $objectAndTo->id = $externalUserId;
        $objectAndTo->inbox = $externalUserId . '/inbox';
        $objectAndTo->outbox = $externalUserId . '/outbox';

        $activityUuid = '68a48176-9847-4806-8227-65199a2da6a3';

        $objectAndToResponseBody = sprintf(
            '{"type": "Person", "id": "%s", "inbox": "%s", "outbox": "%s"}',
            $externalUserId,
            $objectAndTo->inbox,
            $objectAndTo->outbox
        );

        $objectAndToResponse = self::$responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse->getBody()->write($objectAndToResponseBody);

        $requestResolveExternalActor = self::$requestFactory->createRequest('GET', $externalUserId);
        $requestSendUnfollowToRecipient = self::$requestFactory->createRequest('POST', $objectAndTo->inbox)
            ->withHeader('Host', 'example.com')
            ->withHeader('Accept', 'application/activity+json');

        $requestSendUnfollowToRecipient = $this->signRequest($followingUser, $requestSendUnfollowToRecipient);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);

        /** @var ReflectedIdGenerator $idGenerator */
        $idGenerator = $this->getContainer()->get(IdGeneratorInterface::class);

        $idGenerator->setIds([$activityUuid]);

        $followDto = new FollowDto();
        $followDto->to = $objectAndTo->id;
        $followDto->object = $objectAndTo->id;

        $requestSendUnfollowToRecipient->getBody()->write($encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Undo',
            'object' => [
                'type' => 'Follow',
                'object' => $objectAndTo->id,
                'to' => $objectAndTo->id,
            ],
            'actor' => $actorId,
            'id' => sprintf(
                'http://test.localhost/user/%s/activity/%s',
                $followingUser->getUsername(),
                $activityUuid
            ),
            'to' => $objectAndTo->id,
        ], 'application/json'));

        $apiHttpClientMock = $this->getClientMock([
            [
                $requestResolveExternalActor,
                $objectAndToResponse,
            ],
            [
                $requestSendUnfollowToRecipient,
                self::$responseFactory->createResponse(201),
            ],
        ]);

        $this->getContainer()->get('api_http_client')->setMock($apiHttpClientMock);

        // Prepare: create subscription
        $this->getContainer()->get(CommandBusInterface::class)->handle(
            new FollowCommand($followingUser->getActor(), $followDto)
        );

        // Prepare: check subscription
        /** @var ExternalUserRepository $externalUserRepository */
        $externalUserRepository = $this->getContainer()->get(ExternalUserRepository::class);

        $followedUser = $externalUserRepository->findOneByExternalId($externalUserId);

        self::assertNotNull($followedUser);

        /** @var SubscriptionRepositoryInterface $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepositoryInterface::class);

        $subscription = $subscriptionRepository->getByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNotNull($subscription);
        self::assertNull($subscription->getEndDate());

        // Test
        $body = $encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Undo',
            'to' => $objectAndTo->id,
            'object' => [
                'type' => 'Follow',
                'to' => $objectAndTo->id,
                'object' => $objectAndTo->id,
            ],
        ], 'application/json');

        $token = $this->createTokenForUser($followingUser);

        $request = $this->createRequest('POST', sprintf('/user/%s/outbox', $followingUser->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.em');
        $em->clear();

        $subscription = $subscriptionRepository->getByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNull($subscription);
    }
}

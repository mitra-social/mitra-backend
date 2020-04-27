<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Serialization\Encode\EncoderInterface;
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
        $followingUser = $this->createUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();

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

        $requestSendFollowToRecipient->getBody()->write($encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Follow',
            'object' => $objectAndTo->id,
            'actor' => $actorId,
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

        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepository::class);

        $subscription = $subscriptionRepository->findByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNotNull($subscription);
    }

    public function testFollowingSameUserSecondTimeDoesNothing(): void
    {
        $followingUser = $this->createUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();

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

        $requestSendFollowToRecipient->getBody()->write($encoder->encode([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Follow',
            'object' => $objectAndTo->id,
            'actor' => $actorId,
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

        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepository::class);

        $subscription = $subscriptionRepository->findByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNotNull($subscription);
    }

    public function testSuccessfulUnfollow(): void
    {
        // Prepare
        $followingUser = $this->createUser();

        $actorId = sprintf('http://test.localhost/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself.' . uniqid();

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
        $requestSendUnfollowToRecipient = self::$requestFactory->createRequest('POST', $objectAndTo->inbox)
            ->withHeader('Host', 'example.com')
            ->withHeader('Accept', 'application/activity+json');

        $requestSendUnfollowToRecipient = $this->signRequest($followingUser, $requestSendUnfollowToRecipient);

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);

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

        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->getContainer()->get(SubscriptionRepository::class);

        $subscription = $subscriptionRepository->findByActors($followingUser->getActor(), $followedUser->getActor());

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

        $subscription = $subscriptionRepository->findByActors($followingUser->getActor(), $followedUser->getActor());

        self::assertNull($subscription);
    }
}

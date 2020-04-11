<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientResponse;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\ExternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @group Integration
 */
final class OutboxControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testSuccessfulFollow(): void
    {
        $followingUser = $this->createUser();
        $token = $this->createTokenForUser($followingUser);

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->getContainer()->get(ResponseFactoryInterface::class);

        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->getContainer()->get(RequestFactoryInterface::class);

        $activityPubClientMock = $this->getMockBuilder(ActivityPubClient::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept()
            ->getMock();

        $actor = new PersonDto();
        $actor->id = sprintf('https://localhost/users/%s', $followingUser->getUsername());
        $actor->preferredUsername = $followingUser->getUsername();
        $actor->name = $followingUser->getActor()->getName();
        $actor->inbox = sprintf('https://localhost/users/%s/inbox', $followingUser->getUsername());
        $actor->outbox = sprintf('https://localhost/users/%s/outbox', $followingUser->getUsername());

        $externalUserId = 'https://example.com/users/pascalmyself';

        $objectAndTo = new PersonDto();
        $objectAndTo->id = $externalUserId;
        $objectAndTo->inbox = $externalUserId . '/inbox';
        $objectAndTo->outbox = $externalUserId . '/outbox';

        $objectAndToResponse = new ActivityPubClientResponse($responseFactory->createResponse(200), $objectAndTo);

        $request1 = $requestFactory->createRequest('GET', $externalUserId);
        $request2 = $requestFactory->createRequest('GET', $externalUserId);
        $request3 = $requestFactory->createRequest('POST', $objectAndTo->inbox);

        $activityPubClientMock->expects(self::once())->method('signRequest')->with($request3)->willReturn($request3);

        $activityPubClientMock->expects(self::exactly(3))->method('createRequest')
            ->withConsecutive(
                [$request1->getMethod(), (string) $request1->getUri(), null],
                [$request2->getMethod(), (string) $request2->getUri(), null],
                [$request3->getMethod(), (string) $request3->getUri(), $this->callback(
                    function ($value) use ($actor, $externalUserId) {
                        return $value instanceof FollowDto
                            && $value->actor = $actor->id
                            && $value->object = $externalUserId
                            && $value->to = $externalUserId;
                    }
                )]
            )
            ->willReturnOnConsecutiveCalls($request1, $request2, $request3);

        $activityPubClientMock->expects(self::exactly(3))->method('sendRequest')
            ->withConsecutive(
                [$request1],
                [$request2],
                [$request3]
            )->willReturnOnConsecutiveCalls(
                $objectAndToResponse,
                $objectAndToResponse,
                new ActivityPubClientResponse($responseFactory->createResponse(201), null)
            );

        $this->getContainer()->set(ActivityPubClient::class, $activityPubClientMock);

        $body = '{"@context": "https://www.w3.org/ns/activitystreams","type": "Follow", ' .
            '"to": "' . $externalUserId . '", "object": "' . $externalUserId . '"}';

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
}

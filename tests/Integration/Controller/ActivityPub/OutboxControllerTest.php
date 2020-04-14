<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
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

    public function testSuccessfulFollow(): void
    {
        $followingUser = $this->createUser();

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->getContainer()->get(ResponseFactoryInterface::class);

        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->getContainer()->get(RequestFactoryInterface::class);

        $actorId = sprintf('http://localhost:1337/user/%s', $followingUser->getUsername());
        $externalUserId = 'https://example.com/user/pascalmyself';

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

        $objectAndToResponse = $responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/activity+json');

        $objectAndToResponse->getBody()->write($objectAndToResponseBody);

        $request1 = $requestFactory->createRequest('GET', $externalUserId);
        $request2 = $requestFactory->createRequest('GET', $externalUserId);
        $request3 = $requestFactory->createRequest('POST', $objectAndTo->inbox)
            ->withHeader('Host', 'example.com')
            ->withHeader('Accept', 'application/activity+json')
            ->withHeader(
                'Signature',
                'keyId="' . $actorId . '#main-key",' .
                'algorithm="rsa-sha256",headers="(request-target) host accept",' .
                'signature="toOsXSVYLn9sQjP4NcTxBkPGsWcMuwZ37Oe7p7whBZ4w3lFr8YyVsGj8n/6DUmLllsyzn1F+OTqk5C/Gip4UwZ525O' .
                'BaWAvLxDqnlpPi3/e3e26dLG4fTnCgFb/yNBsTYxJ9uvS4PLVREQi1hsbw08DuTve37KrghmvwlmfErqeUCWduDiB+DeEkIAvEl5O' .
                'bMNSWq8nFMmAerickyggmNREnRU3lkK0OzhmbBop/0OmnLByhDL4eJiAcSg7IRUY+mUKq7vazgQqE50lYP1FdEul7t0cImwXCqIUJ' .
                '9jqFAY5v7wkJNExW5pnwleb9j2bFQZeXAcHWvM6bD5gvzjX/ZA=="'
            );

        /** @var EncoderInterface $encoder */
        $encoder = $this->getContainer()->get(EncoderInterface::class);

        $request3->getBody()->write($encoder->encode([
            "@context" => "https://www.w3.org/ns/activitystreams",
            "type" => "Follow",
            "object" => $objectAndTo->id,
            "actor" => $actorId,
            "to" => $objectAndTo->id,
        ], 'application/json'));

        $apiHttpClientMock = $this->getClientMock([
            [
                $request1,
                $objectAndToResponse,
            ],
            [
                $request2,
                $objectAndToResponse,
            ],
            [
                $request3,
                $responseFactory->createResponse(201),
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
}

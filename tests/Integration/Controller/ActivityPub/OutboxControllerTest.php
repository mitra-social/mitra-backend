<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientResponse;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class OutboxControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testReturnsNotFoundForUnknownUser(): void
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = self::$container[ResponseFactoryInterface::class];

        $activityPubClientMock = $this->getMockBuilder(ActivityPubClient::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept([])
            ->getMock();

        $actor = new PersonDto();
        $actor->id = 'https://example.com/users/pascalmyself';
        $actor->preferredUsername = 'PascalMySelf';
        $actor->name = 'Pascal';
        $actor->inbox = 'https://example.com/users/pascalmyself/inbox';
        $actor->outbox = 'https://example.com/users/pascalmyself/outbox';

        $objectAndToResponse = new ActivityPubClientResponse($responseFactory->createResponse(200), $actor);

        $activityPubClientMock->method('sendRequest')->withAnyParameters()->willReturnOnConsecutiveCalls(
            $objectAndToResponse,
            $objectAndToResponse,
            new ActivityPubClientResponse($responseFactory->createResponse(201), null)
        );

        self::$container[ActivityPubClient::class] = $activityPubClientMock;

        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $body = '{"@context": "https://www.w3.org/ns/activitystreams","type": "Follow", ' .
            '"to": "https://example.com/users/pascalmyself", "object": "https://example.com/users/pascalmyself"}';

        $request = $this->createRequest('POST', sprintf('/user/%s/outbox', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);
    }
}

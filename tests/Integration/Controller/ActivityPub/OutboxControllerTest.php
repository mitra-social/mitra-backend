<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\ActivityPub;

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
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $body = '{
  "@context": "https://www.w3.org/ns/activitystreams",
  "type": "Follow",
  "to": "https://mastodon.social/users/pascalmyself",
  "object": "https://mastodon.social/users/pascalmyself"
}';

        $request = $this->createRequest('POST', sprintf('/user/%s/outbox', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);
    }
}

<?php

declare(strict_types=1);

namespace Integration\Controller\Webfinger;

use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class WebfingerControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testWebfingerReturnsResourceInformation(): void
    {
        $user = $this->createUser();
        $resource = sprintf('acct:%s@localhost', $user->getUsername());

        $request = $this->createRequest('GET', sprintf(
            '/.well-known/webfinger?resource=%s',
            $resource
        ));
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);

        $actualPayload = json_decode((string) $response->getBody(), true);
        $expectedPayload = [
            'subject' => $resource,
            'aliases' => [],
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => sprintf('http://localhost:1337/user/%s', $user->getUsername()),
                ],
            ],
        ];

        self::assertEquals($expectedPayload, $actualPayload);
    }

    public function testWebfingerReturnsBadRequestIfResourceQueryParamIsMalformed(): void
    {
        $request = $this->createRequest('GET', sprintf(
            '/.well-known/webfinger?resource=ac:%s@example.org',
            uniqid()
        ));
        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }

    public function testWebfingerReturnsBadRequestIfResourceIdIsMalformed(): void
    {
        $request = $this->createRequest('GET', '/.well-known/webfinger?resource=acct:foo');
        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }

    public function testWebfingerReturnsNotFoundForNotExistingUser(): void
    {
        $request = $this->createRequest('GET', sprintf(
            '/.well-known/webfinger?resource=acct:%s@example.org',
            uniqid()
        ));
        $response = $this->executeRequest($request);

        self::assertStatusCode(404, $response);
    }
}

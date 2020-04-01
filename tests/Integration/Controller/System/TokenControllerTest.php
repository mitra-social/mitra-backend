<?php

declare(strict_types=1);

namespace Integration\Controller\System;

use Firebase\JWT\JWT;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class TokenControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = $this->getContainer()->get(CommandBusInterface::class);
    }

    public function testIssuingTokenFailsBecauseOfMissingData(): void
    {
        $request = $this->createRequest('POST', '/token', '{}');
        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }

    public function testIssuingTokenFailsBecauseOfNotExistingUser(): void
    {
        $data = [
            'username' => 'foo.bar',
            'password' => 'nopassword',
        ];

        $request = $this->createRequest('POST', '/token', json_encode($data));
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testIssuingTokenSuccessful(): void
    {
        $password = 's0mePässw0rd';
        $user = $this->createUser($password);

        $data = [
            'username' => $user->getUsername(),
            'password' => $password,
        ];

        $request = $this->createRequest('POST', '/token', json_encode($data));
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        $payload = (string) $response->getBody();
        $decodedPayload = json_decode($payload, true);

        self::assertArrayHasKey('token', $decodedPayload);

        $decodedToken = JWT::decode($decodedPayload['token'], $this->getContainer()->get('jwt.secret'), ['HS256']);

        self::assertEquals($user->getId(), $decodedToken->userId);
    }
}

<?php

declare(strict_types=1);

namespace Integration\Controller\System;

use Firebase\JWT\JWT;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Entity\User\InternalUser;
use Mitra\Tests\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group Integration
 */
final class TokenControllerTest extends IntegrationTestCase
{

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
        $userId = Uuid::uuid4()->toString();
        $username = 'foo.bar';
        $plaintextPassword = 's0mePässw0rd';

        $user = new InternalUser($userId, $username, 'foo.bar@example.com');
        $user->setPlaintextPassword($plaintextPassword);

        $this->commandBus->handle(new CreateUserCommand($user));

        $data = [
            'username' => $username,
            'password' => $plaintextPassword,
        ];

        $request = $this->createRequest('POST', '/token', json_encode($data));
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);

        $payload = (string) $response->getBody();
        $decodedPayload = json_decode($payload, true);

        self::assertArrayHasKey('token', $decodedPayload);

        $decodedToken = JWT::decode($decodedPayload['token'], $this->getContainer()->get('jwt.secret'), ['HS256']);

        self::assertEquals($userId, $decodedToken->userId);
    }
}

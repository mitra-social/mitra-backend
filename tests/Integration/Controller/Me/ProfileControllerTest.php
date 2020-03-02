<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\Me;

use Firebase\JWT\JWT;
use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\CommandBus\CommandBusInterface;
use Mitra\Entity\User;
use Mitra\Tests\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group Integration
 */
final class ProfileControllerTest extends IntegrationTestCase
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

    public function testReturnsForbiddenIfNotLoggedIn(): void
    {
        $request = $this->createRequest('GET', '/me');
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsUserInformationIfAuthorized(): void
    {
        $userId = Uuid::uuid4()->toString();
        $username = 'foo.bar.2';
        $plaintextPassword = 's0mePässw0rd';

        $user = new User($userId, $username, 'foo.bar.2@example.com');
        $user->setPlaintextPassword($plaintextPassword);

        $this->commandBus->handle(new CreateUserCommand($user));

        $token = JWT::encode(['userId' => $userId], $this->getContainer()->get('jwt.secret'));

        $request = $this->createRequest('GET', '/me', null, ['Authorization' => sprintf('Bearer %s', $token)]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);
    }
}

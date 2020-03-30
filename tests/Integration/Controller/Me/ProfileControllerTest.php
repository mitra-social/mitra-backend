<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\Me;

use Mitra\CommandBus\CommandBusInterface;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class ProfileControllerTest extends IntegrationTestCase
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

    public function testReturnsForbiddenIfNotLoggedIn(): void
    {
        $request = $this->createRequest('GET', '/me');
        $response = $this->executeRequest($request);

        self::assertStatusCode(401, $response);
    }

    public function testReturnsUserInformationIfAuthorized(): void
    {
        $user = $this->createUser();
        $token = $this->createTokenForUser($user);

        $request = $this->createRequest('GET', '/me', null, ['Authorization' => sprintf('Bearer %s', $token)]);
        $response = $this->executeRequest($request);

        self::assertStatusCode(200, $response);
    }
}

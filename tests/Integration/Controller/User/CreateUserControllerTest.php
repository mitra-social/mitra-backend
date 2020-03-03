<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\User;

use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class CreateUserControllerTest extends IntegrationTestCase
{
    public function testCreatingUserFailsWithWrongData(): void
    {
        $request = $this->createRequest('POST', '/user', '{}');
        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }

    public function testUserGetsCreatedSuccessfully(): void
    {
        $userData = [
            'preferredUsername' => 'john.doe',
            'email' => 'john.doe@example.com',
            'password' => 'foobar08',
        ];

        $request = $this->createRequest('POST', '/user', json_encode($userData));
        $response = $this->executeRequest($request);

        self::assertStatusCode(201, $response);
    }
}

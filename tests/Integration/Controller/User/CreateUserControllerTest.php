<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\User;

use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class CreateUserControllerTest extends IntegrationTestCase
{
    public function testSomething(): void
    {
        $request = $this->createRequest('POST', '/user', '{}');
        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration\Controller\User;

use Mitra\Clock\ClockInterface;
use Mitra\Tests\Helper\FreezableClock;
use Mitra\Tests\Integration\CreateUserTrait;
use Mitra\Tests\Integration\IntegrationTestCase;

/**
 * @group Integration
 */
final class UserUpdateControllerTest extends IntegrationTestCase
{
    use CreateUserTrait;

    public function testUpdatingUserWithoutOldPasswordFails(): void
    {
        $password = 'foo';
        $user = $this->createInternalUser($password);
        $token = $this->createTokenForUser($user);
        $body = '{}';

        $request = $this->createRequest('PATCH', sprintf('/user/%s', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);

        $response = $this->executeRequest($request);

        self::assertStatusCode(400, $response);
    }

    public function testUpdatingUserWithWrongOldPasswordFails(): void
    {
        $user = $this->createInternalUser('foo');
        $token = $this->createTokenForUser($user);
        $body = json_encode(['currentPassword' => 'bar']);

        $request = $this->createRequest('PATCH', sprintf('/user/%s', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);

        $response = $this->executeRequest($request);

        self::assertStatusCode(403, $response);
    }

    public function testUpdateUserSuccessfully(): void
    {
        $currentPassword = 'foo';
        $newPassword = 'helloWorld';
        $newEmail = sprintf('updated.email.%s@mitra.social', uniqid());

        $user = $this->createInternalUser($currentPassword);
        $token = $this->createTokenForUser($user);
        $body = json_encode([
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'email' => $newEmail,
        ]);

        $request = $this->createRequest('PATCH', sprintf('/user/%s', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);

        /** @var FreezableClock $clock */
        $clock = $this->getContainer()->get(ClockInterface::class);
        $frozenNow = $clock->freeze();

        $response = $this->executeRequest($request);

        $clock->unfreeze();

        self::assertStatusCode(200, $response);

        $responseData = json_decode((string) $response->getBody(), true);

        self::assertArrayHasKey('email', $responseData);
        self::assertEquals($newEmail, $responseData['email']);
        self::assertArrayHasKey('updated', $responseData);
        self::assertEquals($frozenNow->format('c'), $responseData['updated']);
    }

    public function testUpdateOnlyEmailSuccessfully(): void
    {
        $currentPassword = 'foo';
        $newEmail = sprintf('updated.email.%s@mitra.social', uniqid());

        $user = $this->createInternalUser($currentPassword);
        $token = $this->createTokenForUser($user);
        $body = json_encode([
            'currentPassword' => $currentPassword,
            'email' => $newEmail,
        ]);

        $request = $this->createRequest('PATCH', sprintf('/user/%s', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);

        /** @var FreezableClock $clock */
        $clock = $this->getContainer()->get(ClockInterface::class);
        $frozenNow = $clock->freeze();

        $response = $this->executeRequest($request);

        $clock->unfreeze();

        self::assertStatusCode(200, $response);

        $responseData = json_decode((string) $response->getBody(), true);

        self::assertArrayHasKey('email', $responseData);
        self::assertEquals($newEmail, $responseData['email']);
        self::assertArrayHasKey('updated', $responseData);
        self::assertEquals($frozenNow->format('c'), $responseData['updated']);
    }

    public function testUpdateOnlyPasswordSuccessfully(): void
    {
        $currentPassword = 'foo';
        $newPassword = 'helloWorld';

        $user = $this->createInternalUser($currentPassword);
        $token = $this->createTokenForUser($user);
        $body = json_encode([
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
        ]);

        $request = $this->createRequest('PATCH', sprintf('/user/%s', $user->getUsername()), $body, [
            'Authorization' => sprintf('Bearer %s', $token)
        ]);

        /** @var FreezableClock $clock */
        $clock = $this->getContainer()->get(ClockInterface::class);
        $frozenNow = $clock->freeze();

        $response = $this->executeRequest($request);

        $clock->unfreeze();

        self::assertStatusCode(200, $response);

        $responseData = json_decode((string) $response->getBody(), true);

        self::assertArrayHasKey('email', $responseData);
        self::assertEquals($user->getEmail(), $responseData['email']);
        self::assertArrayHasKey('updated', $responseData);
        self::assertEquals($frozenNow->format('c'), $responseData['updated']);
    }
}

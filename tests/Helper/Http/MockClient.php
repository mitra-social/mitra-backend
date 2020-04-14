<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Http;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockClient implements ClientInterface
{
    /**
     * @var MockObject|ClientInterface
     */
    private $mock;

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (null === $this->mock) {
            throw new \RuntimeException('No client mock set');
        }

        return $this->mock->sendRequest($request);
    }

    public function setMock(ClientInterface $mock): void
    {
        $this->mock = $mock;
    }
}

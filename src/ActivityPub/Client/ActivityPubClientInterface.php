<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use Psr\Http\Message\RequestInterface;

interface ActivityPubClientInterface
{
    public function createRequest(string $method, string $url, ?object $content = null): RequestInterface;

    /**
     * @param RequestInterface $request
     * @return ActivityPubClientResponse
     * @throws ActivityPubClientException
     */
    public function sendRequest(RequestInterface $request): ActivityPubClientResponse;
}

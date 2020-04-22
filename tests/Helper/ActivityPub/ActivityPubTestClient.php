<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\ActivityPub;

use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\Client\ActivityPubClientResponse;
use Psr\Http\Message\RequestInterface;

final class ActivityPubTestClient implements ActivityPubClientInterface
{

    /**
     * @var ActivityPubClientInterface
     */
    private $activityPubClient;

    public function __construct(ActivityPubClientInterface $activityPubClient)
    {
        $this->activityPubClient = $activityPubClient;
    }

    public function createRequest(string $method, string $url, ?object $content = null): RequestInterface
    {
        return $this->activityPubClient->createRequest($method, $url, $content);
    }

    public function signRequest(RequestInterface $request, string $privateKey, string $publicKeyUrl): RequestInterface
    {
        if (!$request->hasHeader('Host')) {
            $request = $request->withHeader('Host', $request->getUri()->getHost());
        }

        return (new Signer(
            new Key($publicKeyUrl, $privateKey),
            Algorithm::create('rsa-sha256'),
            new HeaderList(['(request-target)', 'Host', 'Accept'])
        ))->sign($request);
    }

    public function sendRequest(RequestInterface $request): ActivityPubClientResponse
    {
        return $this->activityPubClient->sendRequest($request);
    }
}

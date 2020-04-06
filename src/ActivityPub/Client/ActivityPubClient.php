<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class ActivityPubClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var ActivityPubDtoPopulator
     */
    private $activityPubDtoPopulator;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ActivityPubDtoPopulator $activityPubDtoPopulator
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->decoder = $decoder;
        $this->encoder = $encoder;
        $this->activityPubDtoPopulator = $activityPubDtoPopulator;
    }

    /**
     * @param string $method
     * @param string $url
     * @param object|null $content
     * @return RequestInterface
     * @throws \Mitra\Serialization\Encode\EncoderException
     */
    public function createRequest(string $method, string $url, ?object $content = null): RequestInterface
    {
        $request =  $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/activity+json');

        if (null !== $content) {
            $encodedType = $this->encoder->encode($content, 'application/json');
            $request = $request->withHeader('Content-Type', 'application/activity+json');
            $request->getBody()->write($encodedType);
        }

        return $request;
    }

    public function signRequest(RequestInterface $request, string $privateKey, string $publicKeyUrl): RequestInterface
    {
        if (!$request->hasHeader('Host')) {
            $request = $request->withHeader('Host', $request->getUri()->getHost());
        }

        if (!$request->hasHeader('Date')) {
            $request = $request->withHeader('Date', (new \DateTimeImmutable())->format(\DateTime::RFC7231));
        }

        return (new Signer(
            new Key($publicKeyUrl, $privateKey),
            Algorithm::create('rsa-sha256'),
            new HeaderList(['(request-target)', 'Host', 'Date', 'Accept'])
        ))->sign($request);
    }

    /**
     * @param RequestInterface $request
     * @return object
     * @throws ActivityPubClientException
     */
    public function sendRequest(RequestInterface $request): object
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ActivityPubClientException(
                $request,
                null,
                sprintf(
                    'Request `%s %s` to remote server failed: %s',
                    $request->getMethod(),
                    (string) $request->getUri(),
                    $e->getMessage()
                ),
                1,
                $e
            );
        }

        if ($response->getStatusCode() > 399) {
            throw new ActivityPubClientException(
                $request,
                $response,
                sprintf(
                    'Request `%s %s` to remote server was answered with HTTP status code `%s`',
                    $request->getMethod(),
                    (string) $request->getUri(),
                    $response->getStatusCode()
                ),
                2
            );
        }

        $decodedBody = $this->decoder->decode((string) $response->getBody(), 'application/json');

        return $this->activityPubDtoPopulator->populate($decodedBody);
    }
}

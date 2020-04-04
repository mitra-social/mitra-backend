<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

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

    /**
     * @var HttpSignature
     */
    private $httpSignature;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ActivityPubDtoPopulator $activityPubDtoPopulator,
        HttpSignature $httpSignature
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->decoder = $decoder;
        $this->encoder = $encoder;
        $this->activityPubDtoPopulator = $activityPubDtoPopulator;
        $this->httpSignature = $httpSignature;
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
        return $this->httpSignature->sign($request, $privateKey, $publicKeyUrl);
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
                sprintf('Request to remote server failed: %s', $e->getMessage()),
                1,
                $e
            );
        }

        if ($response->getStatusCode() > 399) {
            throw new ActivityPubClientException(
                $request,
                $response,
                sprintf(
                    'Requested was answered with HTTP response code `%d` by the remote server',
                    $response->getStatusCode()
                ),
                2
            );
        }

        $decodedBody = $this->decoder->decode((string) $response->getBody(), 'application/json');

        return $this->activityPubDtoPopulator->populate($decodedBody);
    }
}

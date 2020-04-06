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
use Negotiation\AcceptEncoding;
use Negotiation\EncodingNegotiator;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ActivityPubDtoPopulator $activityPubDtoPopulator,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->decoder = $decoder;
        $this->encoder = $encoder;
        $this->activityPubDtoPopulator = $activityPubDtoPopulator;
        $this->logger = $logger;
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
            ->withHeader('Accept', 'application/activity+json')
            ->withHeader('User-Agent', 'mitra-social/0.1');

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
    public function sendRequest(RequestInterface $request): ?object
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

        $responseBody = (string) $response->getBody();

        if ('' === $responseBody) {
            return null;
        }

        $contentTypeHeader = $response->getHeaderLine('Content-Type');

        $negotiator = new EncodingNegotiator();
        /** @var AcceptEncoding $mediaType */
        $mediaType = $negotiator->getBest($contentTypeHeader, ['application/json', 'application/activity+json']);

        if (null === $mediaType) {
            throw new ActivityPubClientException(
                $request,
                $response,
                sprintf('Content-Type `%s` of response from remote server not supported', $contentTypeHeader),
                5
            );
        }

        try {
            $decodedBody = $this->decoder->decode($responseBody, $mediaType->getType());
        } catch (\JsonException $e) {
            throw new ActivityPubClientException($request, $response, sprintf(
                'Could not decode body from remote serve response: %s (body: %s, content-type: %s)',
                $e->getMessage(),
                (string) $response->getBody(),
                $mediaType
            ), 3, $e);
        }

        return $this->activityPubDtoPopulator->populate($decodedBody);
    }
}

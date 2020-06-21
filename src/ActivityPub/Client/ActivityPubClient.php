<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Normalization\NormalizerInterface;
use Mitra\Serialization\Decode\DecoderInterface;
use Mitra\Serialization\Encode\EncoderException;
use Mitra\Serialization\Encode\EncoderInterface;
use Negotiation\AcceptEncoding;
use Negotiation\EncodingNegotiator;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class ActivityPubClient implements ActivityPubClientInterface
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
     * @var NormalizerInterface
     */
    private $normalizer;

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
        NormalizerInterface $normalizer,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ActivityPubDtoPopulator $activityPubDtoPopulator,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->normalizer = $normalizer;
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
     * @throws ActivityPubClientException
     */
    public function createRequest(string $method, string $url, ?object $content = null): RequestInterface
    {
        $request =  $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/activity+json')
            ->withHeader('User-Agent', 'mitra-social/0.1');

        if (null !== $content) {
            try {
                $encodedType = $this->encoder->encode(
                    $this->normalizer->normalize($content),
                    'application/json'
                );
                $request = $request->withHeader('Content-Type', 'application/activity+json');
                $request->getBody()->write($encodedType);
            } catch (EncoderException $e) {
                throw new ActivityPubClientException(
                    null,
                    null,
                    sprintf('Could not encode request body: %s', $e->getMessage()),
                    6,
                    $e
                );
            }
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

        $request = (new Signer(
            new Key($publicKeyUrl, $privateKey),
            Algorithm::create('rsa-sha256'),
            new HeaderList(['(request-target)', 'Host', 'Date', 'Accept'])
        ))->sign($request);

        $this->logger->info('Sign request: ' . $request->getHeaderLine('Signature'));

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @return ActivityPubClientResponse
     * @throws ActivityPubClientException
     */
    public function sendRequest(RequestInterface $request): ActivityPubClientResponse
    {
        $requestBody = (string) $request->getBody();

        $this->logger->info(sprintf(
            'Send request: %s %s (body: %s)',
            $request->getMethod(),
            (string) $request->getUri(),
            '' !== $requestBody ? $requestBody : '<empty>'
        ));

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

        $this->logger->info(sprintf(
            'Received response body for request: %s %s -> %d -> %s',
            $request->getMethod(),
            (string) $request->getUri(),
            $response->getStatusCode(),
            '' !== $responseBody ? $responseBody : '<empty>'
        ));

        if ('' === $responseBody) {
            return new ActivityPubClientResponse($response, null);
        }

        $contentTypeHeader = $response->getHeaderLine('Content-Type');

        $negotiator = new EncodingNegotiator();
        /** @var AcceptEncoding|null $mediaType */
        $mediaType = $negotiator->getBest($contentTypeHeader, [
            'application/activity+json',
            'application/json',
            'application/ld+json'
        ]);

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
                'Could not decode body from remote server response: %s (body: %s, content-type: %s)',
                $e->getMessage(),
                (string) $response->getBody(),
                $mediaType->getType()
            ), 3, $e);
        }

        $object = $this->activityPubDtoPopulator->populate($decodedBody);

        Assert::isInstanceOf($object, ObjectDto::class);

        /** @var ObjectDto $object */

        return new ActivityPubClientResponse($response, $object);
    }
}

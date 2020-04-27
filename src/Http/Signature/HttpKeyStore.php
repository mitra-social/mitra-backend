<?php

declare(strict_types=1);

namespace Mitra\Http\Signature;

use HttpSignatures\Key;
use HttpSignatures\KeyException;
use HttpSignatures\KeyStoreException;
use HttpSignatures\KeyStoreInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class HttpKeyStore implements KeyStoreInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param string $keyId
     * @return Key
     * @throws KeyStoreException
     */
    public function fetch($keyId)
    {
        try {
            $response = $this->httpClient->sendRequest(
                $this->requestFactory->createRequest('GET', $keyId)
                    ->withHeader('Accept', 'application/activity+json, application/json')
            );
        } catch (ClientExceptionInterface $e) {
            throw new KeyStoreException(sprintf(
                'Could not fetch public key for key id `%s`: request failed',
                $keyId
            ), 1, $e);
        }

        if ($response->getStatusCode() > 399) {
            throw new KeyStoreException(sprintf(
                'Could not fetch public key for key id `%s`: remote server answered with HTTP status code `%d`',
                $keyId,
                $response->getStatusCode()
            ), 2);
        }

        $responseBody = (string) $response->getBody();

        try {
            $decodedResponse = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new KeyStoreException(
                sprintf('Could not parse response: %s (body: %s)', $e->getMessage(), $responseBody),
                3,
                $e
            );
        }

        if (!isset($decodedResponse['publicKey']['publicKeyPem'])) {
            throw new KeyStoreException(sprintf(
                'Could not find the public key within the response body under $publicKey.publicKeyPem (body: %s)',
                $responseBody
            ), 4);
        }

        try {
            return new Key($keyId, $decodedResponse['publicKey']['publicKeyPem']);
        } catch (KeyException $e) {
            throw new KeyStoreException(
                sprintf('Could not create key from $publicKey.publicKeyPem: %s', $e->getMessage()),
                5,
                $e
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Mitra\Http\Signature;

use HttpSignatures\Key;
use HttpSignatures\KeyStoreInterface;
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
     * @inheritDoc
     */
    public function fetch($keyId)
    {
        $response = $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('GET', $keyId)
                ->withHeader('Accept', 'application/activity+json, application/json')
        );

        if ($response->getStatusCode() > 399) {
            throw new \RuntimeException(sprintf(
                'Could not fetch public key for key id `%s`: remote server answered with HTTP status code `%d`',
                $keyId,
                $response->getStatusCode()
            ));
        }

        $decodedResponse = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($decodedResponse['publicKey']['publicKeyPem'])) {
            throw new \RuntimeException(
                'Could not find the public key within the response body under /publicKey.publicKeyPem'
            );
        }

        $publicKey = $decodedResponse['publicKey']['publicKeyPem'];

        echo 'Successfully fetched public key from ' , $keyId;

        return new Key($keyId, $publicKey);
    }
}

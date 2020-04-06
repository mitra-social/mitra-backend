<?php

declare(strict_types=1);

namespace Mitra\Middleware;

use Mitra\ActivityPub\Client\HttpSignature;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ValidateHttpSignatureMiddleware
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
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ('' === $request->getHeaderLine('signature')) {
            return $handler->handle($request);
        }

        $valid = HttpSignature::verify($request, function (string $keyId) {
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

            return $decodedResponse['publicKey']['publicKeyPem'];
        });

        if (!$valid) {
            $response = $this->responseFactory->createResponse(401);

            $response->getBody()->write(sprintf(
                'Could not verify signature header with value `%s`',
                $request->getHeaderLine('signature')
            ));

            return $response;
        }

        return $handler->handle($request);
    }
}

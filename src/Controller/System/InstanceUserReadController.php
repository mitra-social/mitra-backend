<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class InstanceUserReadController
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var InternalUser
     */
    private $instanceUser;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UriGenerator $uriGenerator,
        InternalUser $instanceUser
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->uriGenerator = $uriGenerator;
        $this->instanceUser = $instanceUser;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/activity+json');

        $instanceUserUrl = $this->uriGenerator->fullUrlFor('instance-user-read');

        $response->getBody()->write(json_encode([
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'type' => 'Application',
            'id' => $instanceUserUrl,
            'url' => $instanceUserUrl,
            'inbox' => $instanceUserUrl . "/inbox",
            'outbox' => $instanceUserUrl . "/outbox",
            'publicKey' => [
                'id' => $instanceUserUrl . "#main-key",
                'owner' => $instanceUserUrl,
                'publicKeyPem' => $this->instanceUser->getPublicKey(),
            ]
        ]));

        return $response;
    }
}

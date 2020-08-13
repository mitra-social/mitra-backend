<?php

declare(strict_types=1);

namespace Mitra\Controller\System;

use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGeneratorInterface;
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
     * @var UriGeneratorInterface
     */
    private $uriGenerator;

    /**
     * @var InternalUser
     */
    private $instanceUser;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UriGeneratorInterface $uriGenerator,
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

        try {
            $content = json_encode([
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
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $content = $e->getMessage();
        }

        $response->getBody()->write($content);

        return $response;
    }
}

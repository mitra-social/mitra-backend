<?php

declare(strict_types=1);

namespace Mitra\Controller\Webfinger;

use ActivityPhp\Server;
use Doctrine\ORM\EntityRepository;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WebfingerController
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
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     * @param EntityRepository $userRepository
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        EntityRepository $userRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $resource = $request->getQueryParams()['resource'];

        if (0 === preg_match('/^acct:(.+)/i', $resource, $match)) {
            return $this->responseFactory->createResponse(400);
        }

        $handle = $match[1];

        if (2 !== count($handleParts = explode('@', $handle))) {
            return $this->responseFactory->createResponse(400);
        }

        $hostname = $handleParts[1];

        if ($request->getUri()->getHost() !== $hostname) {
            return $this->responseFactory->createResponse(404);
        }

        $preferredUsername = $handleParts[0];

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['preferredUsername' => $preferredUsername]);

        if (null === $user) {
            return $this->responseFactory->createResponse(404);
        }

        $webfinger = new Server\Http\WebFinger(['subject' => $resource, 'aliases' => [], 'links' => [
            [
                'rel' => 'self',
                'type' => 'application/activity+json',
                'href' => 'http://localhost/users/' . $user->getPreferredUsername()
            ]
        ]]);

        $mimeType = 'application/json';
        $response = $this->responseFactory->createResponse(200)->withHeader('Content-Type', $mimeType);

        $response->getBody()->write($this->encoder->encode($webfinger->toArray(), $mimeType));

        return $response;
    }
}

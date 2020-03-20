<?php

declare(strict_types=1);

namespace Mitra\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\UserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class InboxController
{

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UserRepository $userRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('preferredUsername');
        $authenticatedUserId = $request->getAttribute('token')['userId'];

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->userRepository->find($authenticatedUserId);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $inboxUser = $this->userRepository->findOneByPreferredUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        $orderedCollectionDto = new OrderedCollectionDto();

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($orderedCollectionDto, $accept));

        return $response;
    }
}

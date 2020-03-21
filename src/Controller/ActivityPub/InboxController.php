<?php

declare(strict_types=1);

namespace Mitra\Controller\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;
use Mitra\Entity\User;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\UserRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;

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

    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        UserRepository $userRepository,
        RouteCollectorInterface $routeCollector
    ) {
        $this->responseFactory = $responseFactory;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->routeCollector = $routeCollector;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('preferredUsername');
        $authenticatedUserId = $request->getAttribute('token')['userId'];
        $pageNo = $request->getQueryParams()['page'] ?? null;

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $this->userRepository->find($authenticatedUserId);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $inboxUser = $this->userRepository->findOneByPreferredUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        if (null !== $pageNo) {
            $inboxUrl = $this->routeCollector->getRouteParser()->urlFor(
                'user-inbox',
                ['preferredUsername' => $inboxUser->getPreferredUsername()]
            );

            $orderedCollectionDto = new OrderedCollectionPageDto();
            $orderedCollectionDto->partOf = $inboxUrl;
        } else {
            $orderedCollectionDto = new OrderedCollectionDto();
        }

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($orderedCollectionDto, $accept));

        return $response;
    }
}

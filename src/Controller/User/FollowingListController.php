<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\CollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\TypeInterface;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FollowingListController
{
    private const ITEMS_PER_PAGE_LIMIT = 25;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        InternalUserRepository $internalUserRepository,
        UriGenerator $uriGenerator,
        ResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->uriGenerator = $uriGenerator;
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');
        $pageNo = $request->getQueryParams()['page'] ?? null;

        $authenticatedUser = $this->internalUserRepository->resolveFromRequest($request);

        if (null === $authenticatedUser) {
            return $this->responseFactory->createResponse(403);
        }

        if (null === $requestedUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        if ($authenticatedUser->getId() !== $requestedUser->getId()) {
            return $this->responseFactory->createResponse(401);
        }

        $inboxUsername = $requestedUser->getUsername();
        $requestedActor = $requestedUser->getActor();
        $followingRouteName = 'user-following';

        $totalItems = $this->subscriptionRepository->getFollowingCountForActor($requestedActor);
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE_LIMIT);
        $lastPageNo = 0 === $totalPages ? 0 : $totalPages - 1;

        if (null === $pageNo) {
            $collectionDto = new CollectionDto();
            $collectionDto->first = $this->uriGenerator->fullUrlFor(
                $followingRouteName,
                ['username' => $inboxUsername],
                ['page' => 0]
            );
            $collectionDto->last = $this->uriGenerator->fullUrlFor(
                $followingRouteName,
                ['username' => $inboxUsername],
                ['page' => $lastPageNo]
            );
        } else {
            $pageNo = (int) $pageNo;

            if ($pageNo > $lastPageNo) {
                return $this->responseFactory->createResponse(404);
            }

            $followingUrl = $this->uriGenerator->fullUrlFor(
                $followingRouteName,
                ['username' => $inboxUsername]
            );

            $collectionDto = new CollectionPageDto();
            $collectionDto->partOf = $followingUrl;

            if ($pageNo > 0) {
                $collectionDto->prev = $this->uriGenerator->fullUrlFor(
                    $followingRouteName,
                    ['username' => $inboxUsername],
                    ['page' => $pageNo - 1]
                );
            }

            if ($pageNo < $lastPageNo) {
                $collectionDto->next = $this->uriGenerator->fullUrlFor(
                    $followingRouteName,
                    ['username' => $inboxUsername],
                    ['page' => $pageNo + 1]
                );
            }

            $collectionDto->items = $this->getItems($requestedActor, $pageNo);
        }

        $collectionDto->context = TypeInterface::CONTEXT_ACTIVITY_STREAMS;
        $collectionDto->totalItems = $totalItems;

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->encoder->encode($collectionDto, $accept));

        return $response;
    }

    /**
     * @param Actor $actorDto
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     * @throws \Exception
     */
    private function getItems(Actor $actorDto, ?int $page): array
    {
        $offset = null;
        $limit = null;

        if (null !== $page) {
            $offset = $page * self::ITEMS_PER_PAGE_LIMIT;
            $limit = self::ITEMS_PER_PAGE_LIMIT;
        }

        $items = $this->subscriptionRepository->findFollowingActorsForActor(
            $actorDto,
            $offset,
            $limit
        );

        $dtoItems = [];

        foreach ($items as $item) {
            /** @var Subscription $item */
            $subscribedActor = $item->getSubscribedActor();

            if ($subscribedActor instanceof Person) {
                $actorDto = new PersonDto();
            } elseif ($subscribedActor instanceof Organization) {
                $actorDto = new OrganizationDto();
            } else {
                throw new \RuntimeException(sprintf('Unsupported actor class `%s`', get_class($subscribedActor)));
            }

            $subscribedActorUser = $subscribedActor->getUser();

            if ($subscribedActorUser instanceof ExternalUser) {
                $actorDto->id = $subscribedActorUser->getExternalId();
                $actorDto->preferredUsername = $subscribedActorUser->getPreferredUsername();
                $actorDto->inbox = $subscribedActorUser->getInbox();
                $actorDto->outbox = $subscribedActorUser->getOutbox();
            } elseif ($subscribedActorUser instanceof InternalUser) {
                $actorDto->id = $this->uriGenerator->fullUrlFor('user-read', [
                    'username' => $subscribedActorUser->getUsername()
                ]);
                $actorDto->preferredUsername = $subscribedActorUser->getUsername();
                $actorDto->inbox = $this->uriGenerator->fullUrlFor('user-inbox-read', [
                    'username' => $subscribedActorUser->getUsername()
                ]);
                $actorDto->outbox = $this->uriGenerator->fullUrlFor('user-outbox-read', [
                    'username' => $subscribedActorUser->getUsername()
                ]);
            }

            $actorDto->name = $subscribedActor->getName();

            $dtoItems[] = $actorDto;
        }

        return $dtoItems;
    }
}

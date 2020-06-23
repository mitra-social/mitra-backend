<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\CollectionInterface;
use Mitra\Dto\Response\ActivityStreams\CollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\CollectionPageInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionInterface;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\TypeInterface;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Filtering\Filter;
use Mitra\Filtering\FilterFactoryInterface;
use Mitra\Filtering\FilterTokenizer;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Slim\UriGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractCollectionController
{
    protected const ITEMS_PER_PAGE_LIMIT = 25;

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
     * @var FilterFactoryInterface
     */
    private $filterFactory;

    public function __construct(
        InternalUserRepository $internalUserRepository,
        UriGenerator $uriGenerator,
        ResponseFactoryInterface $responseFactory,
        FilterFactoryInterface $filterFactory
    ) {
        $this->internalUserRepository = $internalUserRepository;
        $this->uriGenerator = $uriGenerator;
        $this->responseFactory = $responseFactory;
        $this->filterFactory = $filterFactory;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $accept = $request->getAttribute('accept');
        $username = $request->getAttribute('username');

        $authenticatedUser = $this->internalUserRepository->resolveFromRequest($request);

        if (null === $requestedUser = $this->internalUserRepository->findByUsername($username)) {
            return $this->responseFactory->createResponse(404);
        }

        if (null !== $authenticatedUser && $authenticatedUser->getId() !== $requestedUser->getId()) {
            return $this->responseFactory->createResponse(401);
        }

        $pageNo = $request->getQueryParams()['page'] ?? null;
        $filterQuery = $request->getQueryParams()['filter'] ?? null;

        $filter = is_string($filterQuery) ? $this->filterFactory->create($filterQuery) : null;

        $requestedUsername = $requestedUser->getUsername();
        $requestedActor = $requestedUser->getActor();
        $collectionRouteName = $this->getCollectionRouteName();

        $totalItems = $this->getTotalItemCount($requestedActor, $filter);
        $totalPages = (int) ceil($totalItems / self::ITEMS_PER_PAGE_LIMIT);
        $lastPageNo = 0 === $totalPages ? 0 : $totalPages - 1;

        if (null === $pageNo) {
            $collectionDto = $this->getCollectionDto();
            $collectionDto->setFirst($this->uriGenerator->fullUrlFor(
                $collectionRouteName,
                ['username' => $requestedUsername],
                ['page' => 0]
            ));
            $collectionDto->setLast($this->uriGenerator->fullUrlFor(
                $collectionRouteName,
                ['username' => $requestedUsername],
                ['page' => $lastPageNo]
            ));
        } else {
            $pageNo = (int) $pageNo;

            if ($pageNo > $lastPageNo) {
                return $this->responseFactory->createResponse(404);
            }

            $collectionDto = $this->getCollectionPageDto();
            $collectionDto->setPartOf($this->uriGenerator->fullUrlFor(
                $collectionRouteName,
                ['username' => $requestedUsername]
            ));

            if ($pageNo > 0) {
                $collectionDto->setPrev($this->uriGenerator->fullUrlFor(
                    $collectionRouteName,
                    ['username' => $requestedUsername],
                    ['page' => $pageNo - 1]
                ));
            }

            if ($pageNo < $lastPageNo) {
                $collectionDto->setNext($this->uriGenerator->fullUrlFor(
                    $collectionRouteName,
                    ['username' => $requestedUsername],
                    ['page' => $pageNo + 1]
                ));
            }

            if ($collectionDto instanceof OrderedCollectionInterface) {
                $collectionDto->setOrderedItems($this->getItems($requestedActor, $filter, $pageNo));
            } else {
                $collectionDto->setItems($this->getItems($requestedActor, $filter, $pageNo));
            }
        }

        $collectionDto->setTotalItems($totalItems);

        return $this->responseFactory->createResponseFromDto($collectionDto, $request, $accept);
    }

    protected function getCollectionDto(): CollectionInterface
    {
        return new CollectionDto();
    }

    /**
     * @return CollectionPageInterface
     */
    protected function getCollectionPageDto(): CollectionPageInterface
    {
        return new CollectionPageDto();
    }

    /**
     * @param Actor $requestedActor
     * @param Filter|null $filter
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     */
    abstract protected function getItems(Actor $requestedActor, ?Filter $filter, ?int $page): array;

    abstract protected function getTotalItemCount(Actor $requestedActor, ?Filter $filter): int;

    abstract protected function getCollectionRouteName(): string;
}

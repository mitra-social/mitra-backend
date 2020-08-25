<?php

declare(strict_types=1);

namespace Mitra\Controller\User;

use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Filtering\Filter;
use Mitra\Filtering\FilterFactoryInterface;
use Mitra\Http\Message\ResponseFactoryInterface;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Mitra\Slim\UriGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FollowingListController extends AbstractCollectionController
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var UriGeneratorInterface
     */
    private $uriGenerator;

    /**
     * @var EntityToDtoMapper
     */
    private $entityToDtoMapper;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        InternalUserRepository $internalUserRepository,
        UriGeneratorInterface $uriGenerator,
        ResponseFactoryInterface $responseFactory,
        FilterFactoryInterface $filterFactory,
        EntityToDtoMapper $entityToDtoMapper
    ) {
        parent::__construct($internalUserRepository, $uriGenerator, $responseFactory, $filterFactory);

        $this->subscriptionRepository = $subscriptionRepository;
        $this->uriGenerator = $uriGenerator;
        $this->entityToDtoMapper = $entityToDtoMapper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Actor $actorDto
     * @param Filter|null $filter
     * @param int|null $page
     * @return array<ObjectDto|LinkDto>
     */
    protected function getItems(ServerRequestInterface $request, Actor $actorDto, ?Filter $filter, ?int $page): array
    {
        $offset = null;
        $limit = null;

        if (null !== $page) {
            $offset = $page * self::ITEMS_PER_PAGE_LIMIT;
            $limit = self::ITEMS_PER_PAGE_LIMIT;
        }

        $items = $this->subscriptionRepository->getFollowingActorsForActor(
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
                $dtoClass = $subscribedActor instanceof Person ? PersonDto::class : OrganizationDto::class;
                /** @var ObjectDto $actorDto */
                $actorDto = $this->entityToDtoMapper->map(
                    $subscribedActorUser,
                    $dtoClass,
                    $request
                );
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

            if (null !== $subscribedActor->getIcon()) {
                $actorDto->icon = $subscribedActor->getIcon()->getOriginalUri();
            }

            $dtoItems[] = $actorDto;
        }

        return $dtoItems;
    }

    protected function getTotalItemCount(Actor $requestedActor, ?Filter $filter): int
    {
        return $this->subscriptionRepository->getFollowingCountForActor($requestedActor);
    }

    protected function getCollectionRouteName(): string
    {
        return 'user-following';
    }
}

<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\CollectionPageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionDto;
use Mitra\Dto\Response\ActivityStreams\OrderedCollectionPageDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;
use Slim\Interfaces\RouteResolverInterface;

final class AssignActivityStreamContentToFollowersCommandHandler
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var InternalUserRepository
     */
    private $internalUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @var UriInterface
     */
    private $baseUri;

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;

    /**
     * @var ActivityPubClient
     */
    private $activityPubClient;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        InternalUserRepository $internalUserRepository,
        EntityManagerInterface $entityManager,
        EventEmitterInterface $eventEmitter,
        UriInterface $baseUri,
        RouteResolverInterface $routeResolver,
        UriFactoryInterface $uriFactory,
        ActivityPubClient $activityPubClient
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->entityManager = $entityManager;
        $this->eventEmitter = $eventEmitter;
        $this->baseUri = $baseUri;
        $this->routeResolver = $routeResolver;
        $this->uriFactory = $uriFactory;
        $this->activityPubClient = $activityPubClient;
    }

    public function __invoke(AssignActivityStreamContentToFollowersCommand $command): void
    {
        $entity = $command->getActivityStreamContentEntity();
        $attributedToActor = $entity->getAttributedTo();

        if (null === $attributedToActor) {
            return;
        }

        $audience = $this->determineAssignmentList($command->getActivityStreamDto());

        foreach ($audience as $audienceActor) {
            if (null === $this->subscriptionRepository->getByActors($audienceActor, $entity->getAttributedTo())) {
                // Recipient has not subscribed to content of the content's actor
                continue;
            }

            $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $audienceActor, $entity);
            $this->entityManager->persist($assignment);

            $this->eventEmitter->raise(new ActivityStreamContentAssignedEvent($assignment));
        }
    }

    /**
     * @param ObjectDto $dto
     * @return array<Actor>
     * @throws \Mitra\ActivityPub\Client\ActivityPubClientException
     */
    private function determineAssignmentList(ObjectDto $dto): array
    {
        /** @var array<string|LinkDto|ObjectDto> $mergedRecipientList */
        $mergedRecipientList = array_merge(
            (array) $dto->to ?? [],
            (array) $dto->cc ?? [],
            (array) $dto->bcc ?? [],
            (array) $dto->bto ?? [],
            (array) $dto->audience ?? []
        );

        return $this->getRelevantRecipients($mergedRecipientList);
    }

    /**
     * @param array<string|LinkDto|ObjectDto> $recipientList
     * @return array<Actor>
     * @throws \Mitra\ActivityPub\Client\ActivityPubClientException
     */
    private function getRelevantRecipients(array $recipientList): array
    {
        /** @var array<string> $filteredRecipientList */
        $filteredRecipientList = array_filter($recipientList, function ($value): bool {
            // TODO: support other stuff than link representations as strings
            return is_string($value);
        });

        $baseUriAsString = (string) $this->baseUri;
        $actors = [];

        foreach ($filteredRecipientList as $recipient) {
            if (0 !== strpos($recipient, $baseUriAsString)) {
                $response = $this->activityPubClient->sendRequest(
                    $this->activityPubClient->createRequest('GET', $recipient)
                );
                $responseObject = $response->getReceivedObject();

                if ($responseObject instanceof CollectionDto) {
                    $actors = array_merge($actors, $this->getRelevantRecipients(
                        $this->getItemsFromCollection($responseObject)
                    ));
                }

                continue;
            }

            $actorUrl = $this->uriFactory->createUri($recipient);

            $routingResult = $this->routeResolver->computeRoutingResults($actorUrl->getPath(), 'GET');

            $username = $routingResult->getRouteArguments()['username'];

            if (null === $user = $this->internalUserRepository->findByUsername($username)) {
                continue;
            }

            $actors[] = $user->getActor();
        }

        return $actors;
    }

    /**
     * @param CollectionDto $collection
     * @return array<string|LinkDto|ObjectDto>
     * @throws \Mitra\ActivityPub\Client\ActivityPubClientException
     */
    private function getItemsFromCollection(CollectionDto $collection): array
    {
        if ($collection instanceof OrderedCollectionDto && null !== $collection->orderedItems) {
            return $collection->orderedItems;
        }

        if (null !== $collection->items) {
            return $collection->items;
        }

        if (null !== $collection->first) {
            $items = [];
            $next = $collection->first;

            while (null !== $next) {
                $response = $this->activityPubClient->sendRequest(
                    $this->activityPubClient->createRequest('GET', (string) $collection->first)
                );
                $objectResponse = $response->getReceivedObject();

                if ($objectResponse instanceof OrderedCollectionPageDto) {
                    $items = array_merge($items, $objectResponse->orderedItems);
                    $next = $objectResponse->next;
                    continue;
                }

                if ($objectResponse instanceof CollectionPageDto) {
                    $items = array_merge($items, $objectResponse->items);
                    $next = $objectResponse->next;
                    continue;
                }

                $next = null;
            }

            return $items;
        }

        return [];
    }
}

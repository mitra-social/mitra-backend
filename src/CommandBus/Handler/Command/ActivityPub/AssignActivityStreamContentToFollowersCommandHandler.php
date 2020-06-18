<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Client\ActivityPubClientInterface;
use Mitra\ActivityPub\CollectionIterator;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Repository\InternalUserRepository;
use Mitra\Repository\SubscriptionRepository;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Interfaces\RouteResolverInterface;

final class AssignActivityStreamContentToFollowersCommandHandler
{
    private const PUBLIC_URL = 'https://www.w3.org/ns/activitystreams#Public';

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
     * @var ActivityPubClientInterface
     */
    private $activityPubClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        InternalUserRepository $internalUserRepository,
        EntityManagerInterface $entityManager,
        EventEmitterInterface $eventEmitter,
        UriInterface $baseUri,
        RouteResolverInterface $routeResolver,
        UriFactoryInterface $uriFactory,
        ActivityPubClientInterface $activityPubClient,
        LoggerInterface $logger
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->entityManager = $entityManager;
        $this->eventEmitter = $eventEmitter;
        $this->baseUri = $baseUri;
        $this->routeResolver = $routeResolver;
        $this->uriFactory = $uriFactory;
        $this->activityPubClient = $activityPubClient;
        $this->logger = $logger;
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
     * @param iterable<string|LinkDto|ObjectDto> $recipientList
     * @return array<Actor>
     * @throws \Mitra\ActivityPub\Client\ActivityPubClientException
     */
    private function getRelevantRecipients(iterable $recipientList): array
    {
        $baseUriAsString = (string) $this->baseUri;
        $actors = [];

        foreach ($recipientList as $recipient) {
            if ($recipient instanceof LinkDto) {
                $recipient = (string) $recipient;
            }

            if (!is_string($recipient) || self::PUBLIC_URL === $recipient) {
                // TODO: support objects as well
                continue;
            }

            if (0 !== strpos($recipient, $baseUriAsString)) {
                // External resource
                /*try {
                    $response = $this->activityPubClient->sendRequest(
                        $this->activityPubClient->createRequest('GET', $recipient)
                    );
                } catch (ActivityPubClientException $e) {
                    $this->logger->info(sprintf('Could not fetch external recipient with id `%s`', $recipient));
                    continue;
                }

                $responseObject = $response->getReceivedObject();

                if ($responseObject instanceof CollectionDto) {
                    $actors = array_merge($actors, $this->getRelevantRecipients(
                        new CollectionIterator($this->activityPubClient, $responseObject)
                    ));
                }*/
            } else {
                // Internal resource
                $actorUrl = $this->uriFactory->createUri($recipient);
                $routingResult = $this->routeResolver->computeRoutingResults($actorUrl->getPath(), 'GET');
                $username = $routingResult->getRouteArguments()['username'];

                if (null === $user = $this->internalUserRepository->findByUsername($username)) {
                    continue;
                }

                $actors[] = $user->getActor();
            }
        }

        return $actors;
    }
}

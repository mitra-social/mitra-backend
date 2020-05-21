<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\Command\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\AssignActivityStreamContentToFollowersCommand;
use Mitra\CommandBus\Event\ActivityPub\ActivityStreamContentAssignedEvent;
use Mitra\CommandBus\EventEmitterInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
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

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        InternalUserRepository $internalUserRepository,
        EntityManagerInterface $entityManager,
        EventEmitterInterface $eventEmitter,
        UriInterface $baseUri,
        RouteResolverInterface $routeResolver,
        UriFactoryInterface $uriFactory
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->internalUserRepository = $internalUserRepository;
        $this->entityManager = $entityManager;
        $this->eventEmitter = $eventEmitter;
        $this->baseUri = $baseUri;
        $this->routeResolver = $routeResolver;
        $this->uriFactory = $uriFactory;
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
     */
    private function determineAssignmentList(ObjectDto $dto): array
    {
        $recipientList = array_merge(
            $dto->to ?? [],
            $dto->cc ?? [],
            $dto->bcc ?? [],
            $dto->bto ?? [],
            $dto->audience ?? [],
        );

        $filteredRecipientList = array_filter($recipientList, function ($value) {
            if (!is_string($value)) {
                // TODO: support other stuff than link representations as strings
                return false;
            }

            if (0 !== strpos($value, (string) $this->baseUri)) {
                return false;
            }

            return true;
        });

        $actors = [];

        foreach ($filteredRecipientList as $recipient) {
            $actorUrl = $this->uriFactory->createUri($recipient);

            $routingResult = $this->routeResolver->computeRoutingResults($actorUrl->getPath(), 'GET');

            $username = $routingResult->getRouteArguments()['username'];

            if (null === $user =  $this->internalUserRepository->findByUsername($username)) {
                continue;
            }

            $actors[] = $user->getActor();
        }

        return $actors;
    }
}

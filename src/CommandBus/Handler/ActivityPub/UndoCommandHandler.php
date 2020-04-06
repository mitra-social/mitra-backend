<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Type\ActorInterface;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ExternalUserRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class UndoCommandHandler
{
    /**
     * @var ExternalUserRepository
     */
    private $externalUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ActivityPubClient
     */
    private $activityPubClient;

    /**
     * @var EntityToDtoMapper
     */
    private $entityToDtoMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ExternalUserRepository $externalUserRepository,
        EntityManagerInterface $entityManager,
        ActivityPubClient $activityPubClient,
        EntityToDtoMapper $entityToDtoMapper,
        LoggerInterface $logger
    ) {
        $this->externalUserRepository = $externalUserRepository;
        $this->entityManager = $entityManager;
        $this->activityPubClient = $activityPubClient;
        $this->entityToDtoMapper = $entityToDtoMapper;
        $this->logger = $logger;
    }

    public function __invoke(UndoCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $undo = $command->getUndoDto();
        $to = $this->getLinkOrObject($undo->to);

        $externalUser = null;

        if (null !== $objectId = $this->getIdFromObject($to)) {
            $externalUser = $this->externalUserRepository->findOneByExternalId(hash('sha256', $objectId));
        }

        if (null === $externalUser) {
            $externalUser = $this->createExternalUser($to);
        }

        $undo->actor = $this->entityToDtoMapper->map($commandActor, PersonDto::class);

        try {
            $undoRequest = $this->activityPubClient->signRequest(
                $this->activityPubClient->createRequest('POST', $externalUser->getInbox(), $undo),
                $commandActorUser->getPrivateKey(),
                $undo->actor->id . '#main-key'
            );

            $response = $this->activityPubClient->sendRequest($undoRequest);

            print_r($response);
        } catch (ActivityPubClientException $e) {
            $context = [];

            if (null !== $response = $e->getResponse()) {
                $context['responseBody'] = (string) $response->getBody();
            }

            $this->logger->error($e->getMessage(), $context);
        }
    }

    /**
     * Tries to extract the object id out of all the possible values
     * @param object|LinkDto|ObjectDto $object
     * @return string|null
     */
    private function getIdFromObject(object $object): ?string
    {
        if ($object instanceof LinkDto) {
            return $object->id;
        } elseif ($object instanceof ObjectDto) {
            return $object->id;
        }

        return null;
    }

    private function createExternalUser($object): ExternalUser
    {
        $actorData = $this->fetchUserData($object);

        $externalUser = new ExternalUser(
            Uuid::uuid4()->toString(),
            $actorData['id'],
            hash('sha256', $actorData['id']),
            $actorData['preferredUsername'],
            $actorData['inbox'],
            $actorData['outbox']
        );

        $actor = new Actor($externalUser);
        $actor->setName($actorData['name']);
        $actor->setIcon($actorData['icon']);

        $externalUser->setActor($actor);

        return $externalUser;
    }

    private function normalizeProperty($object, $propertyName): ?string
    {
        if (!property_exists($object, $propertyName) || null === $object->$propertyName) {
            return null;
        }

        $propertyValue = $object->$propertyName;

        if (is_string($propertyValue)) {
            return $propertyValue;
        }

        if ($propertyValue instanceof LinkDto) {
            return $propertyValue->href;
        }

        return null;
    }

    /**
     * @param ObjectDto|object $object
     * @return array
     */
    private function fetchUserData(object $object): array
    {
        $propertiesLocal = [
            'id' => null,
            'preferredUsername' => null,
            'inbox' => null,
            'outbox' => null,
            'name' => null,
            'icon' => null,
            'following' => null,
            'followers' => null,
            'url' => null,
        ];

        if ($object instanceof ObjectDto) {
            $propertiesLocal = [
                'id' => $this->normalizeProperty($object, 'id'),
                'preferredUsername' => $this->normalizeProperty($object, 'preferredUsername'),
                'inbox' => $this->normalizeProperty($object, 'inbox'),
                'outbox' => $this->normalizeProperty($object, 'outbox'),
                'name' => $this->normalizeProperty($object, 'name'),
                'icon' => $this->normalizeProperty($object, 'icon'),
                'following' => $this->normalizeProperty($object, 'following'),
                'followers' => $this->normalizeProperty($object, 'followers'),
                'url' => $this->normalizeProperty($object, 'url'),
            ];
        }

        $userUrl = $this->getObjectUrl($object) ?? $object->id;

        if (null === $userUrl) {
            return $propertiesLocal;
        }

        $propertiesRemote = [];

        try {
            /** @var ActorInterface $response */
            $response = $this->activityPubClient->sendRequest(
                $this->activityPubClient->createRequest('GET', $userUrl)
            );

            $propertiesRemote = [
                'id' => $this->normalizeProperty($response, 'id'),
                'preferredUsername' => $this->normalizeProperty($response, 'preferredUsername'),
                'inbox' => $this->normalizeProperty($response, 'inbox'),
                'outbox' => $this->normalizeProperty($response, 'outbox'),
                'name' => $this->normalizeProperty($response, 'name'),
                'icon' => $this->normalizeProperty($response, 'icon'),
                'following' => $this->normalizeProperty($response, 'following'),
                'followers' => $this->normalizeProperty($response, 'followers'),
                'url' => $this->normalizeProperty($response, 'url'),
            ];
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Could not get remote object from url `%s`: %s', $userUrl, $e->getMessage()));
        }

        return array_filter($propertiesRemote) + $propertiesLocal;
    }

    /**
     * @param null|string|LinkDto|ObjectDto $value
     * @return object
     */
    private function getLinkOrObject($value): ?object
    {
        if (is_string($value)) {
            $link = new LinkDto();
            $link->href = $value;
            
            return $link;
        }
        
        return $value;
    }

    private function getObjectUrl(object $object): ?string
    {
        if ($object instanceof LinkDto) {
            return $object->href;
        }

        if ($object instanceof ObjectDto) {
            if (null !== $object->url) {
                $firstUrl = is_array($object->url) ? array_shift($object->url) : $object->url;
                return is_object($firstUrl) ? $this->getObjectUrl($firstUrl) : $firstUrl;
            }
        }

        return null;
    }
}

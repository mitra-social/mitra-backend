<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\ActivityPub\Client\ActivityPubClient;
use Mitra\ActivityPub\Client\ActivityPubClientException;
use Mitra\ActivityPub\Type\ActorInterface;
use Mitra\CommandBus\Command\ActivityPub\FollowCommand;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ExternalUserRepository;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class FollowCommandHandler
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

    public function __construct(
        ExternalUserRepository $externalUserRepository,
        EntityManagerInterface $entityManager,
        ActivityPubClient $activityPubClient,
        EntityToDtoMapper $entityToDtoMapper
    ) {
        $this->externalUserRepository = $externalUserRepository;
        $this->entityManager = $entityManager;
        $this->activityPubClient = $activityPubClient;
        $this->entityToDtoMapper = $entityToDtoMapper;
    }

    public function __invoke(FollowCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $follow = $command->getFollowDto();
        $object = $follow->object;

        if (null === $objectId = $this->getIdFromObject($object)) {
            throw new \RuntimeException('Could not determine id of object');
        }

        $externalUser = $this->externalUserRepository->findOneByExternalId(hash('sha256', $objectId));

        if (null === $externalUser) {
            $externalUser = $this->createExternalUser($object);
        }

        $subscription = new Subscription(
            Uuid::uuid4()->toString(),
            $command->getActor(),
            $externalUser->getActor(),
            new \DateTime()
        );

        $follow->actor = $this->entityToDtoMapper->map($commandActor, PersonDto::class);

        try {
            $followRequest = $this->activityPubClient->signRequest(
                $this->activityPubClient->createRequest('POST', $externalUser->getInbox(), $follow),
                $commandActorUser->getPrivateKey(),
                $follow->actor->id
            );

            $response = $this->activityPubClient->sendRequest($followRequest);

            print_r($response);
        } catch (ActivityPubClientException $e) {
            echo $e->getMessage() , PHP_EOL;
            echo (string) $e->getResponse()->getBody();
        }

        exit;

        $this->entityManager->persist($subscription);
        //$this->entityManager->flush();
    }

    /**
     * Tries to extract the object id out of all the possible values
     * @param string|LinkDto|ObjectDto $object
     * @return string|null
     */
    private function getIdFromObject($object): ?string
    {
        if (is_string($object)) {
            return $object;
        } elseif (!is_object($object)) {
            return null;
        } elseif ($object instanceof LinkDto) {
            return $object->href;
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
        $propertiesRemote = [];
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

        try {
            $userUrl = $this->normalizeProperty($object, 'url') ?? $object->id;

            if (null !== $userUrl) {
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
                ];
            }
        } catch (\Exception $e) {
        }

        return array_filter($propertiesRemote) + $propertiesLocal;
    }
}

<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Mitra\Repository\ExternalUserRepository;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class ExternalUserResolver
{
    /**
     * @var RemoteObjectResolver
     */
    private $remoteObjectResolver;

    /**
     * @var ExternalUserRepository
     */
    private $externalUserRepository;

    public function __construct(
        RemoteObjectResolver $remoteObjectResolver,
        ExternalUserRepository $externalUserRepository
    ) {
        $this->remoteObjectResolver = $remoteObjectResolver;
        $this->externalUserRepository = $externalUserRepository;
    }

    /**
     * @param string|ObjectDto|LinkDto $object
     * @return null|ExternalUser
     * @throws \Mitra\ActivityPub\Resolver\RemoteObjectResolverException
     */
    public function resolve($object): ?ExternalUser
    {
        Assert::notNull($object);

        $externalId = null;

        if (is_string($object)) {
            $externalId = $object;
        } elseif ($object instanceof LinkDto) {
            $externalId = $object->href;
        } elseif ($object instanceof ObjectDto) {
            $externalId = $object->id;
        }

        if (null !== $externalUser = $this->externalUserRepository->findOneByExternalId($externalId)) {
            return $externalUser;
        }

        if (null === $resolvedObject = $this->remoteObjectResolver->resolve($object)) {
            return null;
        }

        Assert::isInstanceOf($resolvedObject, ActorInterface::class);

        $externalId = $resolvedObject->getId();

        // Check again because maybe the provided string was not really the object id itself in the first place but
        // just an url to fetch the actor
        if (null !== $externalUser = $this->externalUserRepository->findOneByExternalId($externalId)) {
            return $externalUser;
        }

        $externalUser = new ExternalUser(
            Uuid::uuid4()->toString(),
            $externalId,
            hash('sha256', $externalId),
            $resolvedObject->getPreferredUsername(),
            $resolvedObject->getInbox(),
            $resolvedObject->getOutbox()
        );

        if ('Person' === $resolvedObject->type) {
            $actor = new Person($externalUser);
        } elseif ('Organization' === $resolvedObject->type) {
            $actor = new Organization($externalUser);
        } else {
            throw new \RuntimeException(sprintf('Unsupported actor type `%s`', $resolvedObject->type));
        }

        $actor->setName($resolvedObject->getName());
        //$actor->setIcon($resolvedObject->getIcon()); could be array... which one to choose then?

        $externalUser->setActor($actor);

        return $externalUser;
    }
}

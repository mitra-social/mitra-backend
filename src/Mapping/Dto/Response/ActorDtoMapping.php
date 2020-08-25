<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Mitra\Mapping\Dto\EntityToDtoMappingContext;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;

abstract class ActorDtoMapping implements EntityToDtoMappingInterface
{
    public static function getEntityClass(): string
    {
        return ExternalUser::class;
    }

    /**
     * @param object|ExternalUser $entity
     * @param EntityToDtoMappingContext $context
     * @return object
     * @throws InvalidEntityException
     */
    public function toDto(object $entity, EntityToDtoMappingContext $context): object
    {
        if (!$entity instanceof ExternalUser) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $actor = $entity->getActor();

        if ($actor instanceof Person) {
            $dto = new UserResponseDto();
            $dto->internalUserId = $actor->getUser()->getId();
        } elseif ($actor instanceof Organization) {
            $dto = new OrganizationDto();
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Could not determine type for `%s`',
                get_class($actor)
            ));
        }

        $dto->id = $entity->getExternalId();
        $dto->preferredUsername = $entity->getPreferredUsername();
        $dto->name = $actor->getName();

        $dto->inbox = $entity->getInbox();
        $dto->outbox = $entity->getOutbox();

        if (null !== $actor->getIcon()) {
            $dto->icon = $actor->getIcon()->getOriginalUri();
        }

        return $dto;
    }
}

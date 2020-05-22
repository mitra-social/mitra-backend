<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;
use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
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
     * @return object
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof ExternalUser) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $actor = $entity->getActor();

        if ($actor instanceof Person) {
            $dto = new PersonDto();
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

        return $dto;
    }
}

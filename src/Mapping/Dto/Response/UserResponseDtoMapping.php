<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\UserResponseDto;
use Mitra\Entity\User;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;

final class UserResponseDtoMapping implements EntityToDtoMappingInterface
{

    public static function getDtoClass(): string
    {
        return UserResponseDto::class;
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    /**
     * @param object|User $entity
     * @return object|UserResponseDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof User) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $userResponseDto = new UserResponseDto();

        $userResponseDto->id = $entity->getId();
        $userResponseDto->email = $entity->getEmail();
        $userResponseDto->preferredUsername = $entity->getPreferredUsername();
        $userResponseDto->registeredAt = $entity->getCreatedAt()->format('c');

        return $userResponseDto;
    }
}

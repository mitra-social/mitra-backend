<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Request;

use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Entity\User;
use Mitra\Mapping\Dto\DtoToEntityMappingInterface;
use Mitra\Mapping\Dto\InvalidDtoException;
use Ramsey\Uuid\Uuid;

final class CreateUserRequestDtoMapping implements DtoToEntityMappingInterface
{
    public static function getDtoClass(): string
    {
        return CreateUserRequestDto::class;
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    /**
     * @param object|CreateUserRequestDto $dto
     * @return object|User
     * @throws \Exception
     */
    public function toEntity(object $dto): object
    {
        if (!$dto instanceof CreateUserRequestDto) {
            throw InvalidDtoException::fromDto($dto, static::getDtoClass());
        }

        $user = new User(Uuid::uuid4()->toString(), $dto->preferredUsername, $dto->email);

        $user->setPlaintextPassword($dto->password);

        return $user;
    }
}

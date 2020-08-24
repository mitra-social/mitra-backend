<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Request;

use Mitra\Dto\Request\CreateUserRequestDto;
use Mitra\Dto\Request\UpdateUserRequestDto;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\InternalUser;
use Mitra\Mapping\Dto\DtoToEntityMappingInterface;
use Mitra\Mapping\Dto\InvalidDtoException;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class UpdateUserRequestDtoMapping implements DtoToEntityMappingInterface
{
    public static function getDtoClass(): string
    {
        return UpdateUserRequestDto::class;
    }

    public static function getEntityClass(): string
    {
        return InternalUser::class;
    }

    /**
     * @param object|CreateUserRequestDto $dto
     * @param object|null $user
     * @return object|InternalUser
     * @throws InvalidDtoException
     */
    public function toEntity(object $dto, ?object $user): object
    {
        if (!$dto instanceof UpdateUserRequestDto) {
            throw InvalidDtoException::fromDto($dto, static::getDtoClass());
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Cannot instantiate new object for update');
        }

        Assert::isInstanceOf($user, InternalUser::class);

        /** @var InternalUser $user */

        if (null !== $dto->newPassword) {
            $user->setPlaintextPassword($dto->newPassword);
        }

        if (null !== $dto->email) {
            $user->setEmail($dto->email);
        }

        return $user;
    }
}

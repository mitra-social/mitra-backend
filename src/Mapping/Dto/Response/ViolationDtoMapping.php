<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ViolationDto;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;
use Mitra\Validator\Violation;

final class ViolationDtoMapping implements EntityToDtoMappingInterface
{

    public static function getDtoClass(): string
    {
        return ViolationDto::class;
    }

    public static function getEntityClass(): string
    {
        return Violation::class;
    }

    /**
     * @param object|Violation $entity
     * @return object|ViolationDto
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object
    {
        if (!$entity instanceof Violation) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $violationDto = new ViolationDto();

        $violationDto->code = $entity->getCode();
        $violationDto->message = (string) $entity->getMessage();
        $violationDto->parameters = $entity->getParameters();
        $violationDto->invalidValue = $entity->getInvalidValue();
        $violationDto->propertyPath = $entity->getPropertyPath();

        return $violationDto;
    }
}

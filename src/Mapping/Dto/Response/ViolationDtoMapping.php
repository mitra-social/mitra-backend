<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ViolationDto;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
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
     * @return object
     */
    public function toDto(object $entity): object
    {
        $violationDto = new ViolationDto();

        $violationDto->code = $entity->getCode();
        $violationDto->message = $entity->getMessage();
        $violationDto->parameters = $entity->getParameters();
        $violationDto->invalidValue = $entity->getInvalidValue();
        $violationDto->propertyPath = $entity->getPropertyPath();

        return $violationDto;
    }
}

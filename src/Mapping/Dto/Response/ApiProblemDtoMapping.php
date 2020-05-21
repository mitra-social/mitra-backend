<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\ApiProblem\ApiProblemInterface;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;

final class ApiProblemDtoMapping implements EntityToDtoMappingInterface
{

    public static function getDtoClass(): string
    {
        return ApiProblemDto::class;
    }

    public static function getEntityClass(): string
    {
        return ApiProblemInterface::class;
    }

    public function toDto(object $entity): object
    {
        if (!$entity instanceof ApiProblemInterface) {
            throw InvalidEntityException::fromEntity($entity, static::getEntityClass());
        }

        $dto = new ApiProblemDto();

        $dto->instance = $entity->getInstance();
        $dto->title = $entity->getTitle();
        $dto->detail = $entity->getDetail();
        $dto->type = $entity->getType();

        return $dto;
    }
}

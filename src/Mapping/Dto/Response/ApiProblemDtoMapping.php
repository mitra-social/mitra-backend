<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\ApiProblem\ApiProblem;
use Mitra\ApiProblem\ApiProblemInterface;
use Mitra\Dto\Response\ApiProblemDto;
use Mitra\Mapping\Dto\EntityToDtoMappingInterface;
use Mitra\Mapping\Dto\InvalidEntityException;

class ApiProblemDtoMapping implements EntityToDtoMappingInterface
{

    public static function getDtoClass(): string
    {
        return ApiProblemDto::class;
    }

    public static function getEntityClass(): string
    {
        return ApiProblem::class;
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

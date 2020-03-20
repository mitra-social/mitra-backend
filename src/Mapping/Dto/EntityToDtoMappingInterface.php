<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

interface EntityToDtoMappingInterface extends DtoMappingInterface
{
    /**
     * @param object $entity
     * @return object
     * @throws InvalidEntityException
     */
    public function toDto(object $entity): object;
}

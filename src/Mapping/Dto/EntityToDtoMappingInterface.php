<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

interface EntityToDtoMappingInterface extends DtoMappingInterface
{
    public function toDto(object $entity, EntityToDtoMappingContext $context): object;
}

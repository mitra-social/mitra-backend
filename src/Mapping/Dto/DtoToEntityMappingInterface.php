<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

interface DtoToEntityMappingInterface extends DtoMappingInterface
{
    public function toEntity(object $dto): object;
}

<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

interface DtoMappingInterface
{
    public static function getDtoClass(): string;

    public static function getEntityClass(): string;
}

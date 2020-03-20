<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

final class InvalidEntityException extends \Exception
{
    public static function fromEntity(object $actualEntity, string $expectedEntityClass): self
    {
        return new static(sprintf(
            'Entity of type `%s` expected, `%s` given',
            $expectedEntityClass,
            get_class($actualEntity)
        ));
    }
}

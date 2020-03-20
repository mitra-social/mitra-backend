<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

final class InvalidDtoException extends \Exception
{
    public static function fromDto(object $actualDto, string $expectedDtoClass): self
    {
        return new static(sprintf(
            'Dto of type `%s` expected, `%s` given',
            $expectedDtoClass,
            get_class($actualDto)
        ));
    }
}

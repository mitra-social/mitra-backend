<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;

final class PersonDtoMapping extends ActorDtoMapping
{
    public static function getDtoClass(): string
    {
        return PersonDto::class;
    }
}

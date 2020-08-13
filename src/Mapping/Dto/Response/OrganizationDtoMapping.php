<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

use Mitra\Dto\Response\ActivityPub\Actor\OrganizationDto;

final class OrganizationDtoMapping extends ActorDtoMapping
{
    public static function getDtoClass(): string
    {
        return OrganizationDto::class;
    }
}

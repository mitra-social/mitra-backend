<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\Actor\OrganizationDto as ActivityStreamsOrganizationDto;

class OrganizationDto extends ActivityStreamsOrganizationDto implements ActorInterface
{
    use ActorTrait;
}

<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\Actor\PersonDto as ActivityStreamsPersonDto;

class PersonDto extends ActivityStreamsPersonDto implements ActorInterface
{
    use ActorTrait;

    public $context = [
        'https://www.w3.org/ns/activitystreams',
        'https://w3id.org/security/v1',
    ];
}

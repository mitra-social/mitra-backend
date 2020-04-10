<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\Actor\PersonDto as ActivityStreamsPerson;

class PersonDto extends ActivityStreamsPerson implements ActorInterface
{
    use ActorTrait;
}

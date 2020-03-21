<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityPub\Actor;

use Mitra\Dto\Response\ActivityStreams\Actor\Person as ActivityStreamsPerson;

class Person extends ActivityStreamsPerson
{
    use ActorTrait;
}

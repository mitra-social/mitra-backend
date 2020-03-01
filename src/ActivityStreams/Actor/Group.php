<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams\Actor;

use Mitra\ActivityStreams\AbstractObject;

final class Group extends AbstractObject implements ApplicationInterface
{
    public static function getType(): ?string
    {
        return 'Group';
    }
}

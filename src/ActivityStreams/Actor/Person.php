<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams\Actor;

use Mitra\ActivityStreams\AbstractObject;

final class Person extends AbstractObject implements PersonInterface
{
    public static function getType(): ?string
    {
        return 'Person';
    }
}

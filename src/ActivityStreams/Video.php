<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

final class Video extends AbstractObject implements VideoInterface
{
    public static function getType(): ?string
    {
        return 'Video';
    }
}

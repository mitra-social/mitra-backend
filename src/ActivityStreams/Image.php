<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

final class Image extends AbstractObject implements ImageInterface
{
    public static function getType(): ?string
    {
        return 'Image';
    }
}

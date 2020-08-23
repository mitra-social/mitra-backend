<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Resolver;

use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

final class ObjectIdDeterminer
{
    /**
     * @param ObjectDto|string|LinkDto|null $object
     * @return string|null
     */
    public function getId($object): ?string
    {
        if (null === $object || is_string($object)) {
            return $object;
        }

        if ($object instanceof LinkDto) {
            return $object->href;
        }

        return $object->id;
    }
}

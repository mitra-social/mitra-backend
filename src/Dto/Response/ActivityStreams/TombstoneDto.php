<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class TombstoneDto extends ObjectDto
{
    /**
     * @var string
     */
    public $type = 'Tombstone';

    /**
     * On a Tombstone object, the formerType property identifies the type of the object that was deleted.
     * @var null|ObjectDto
     */
    public $formerType;

    /**
     * On a Tombstone object, the deleted property is a timestamp for when the object was deleted.
     * @var null|string
     */
    public $deleted;
}

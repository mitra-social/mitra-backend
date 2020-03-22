<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class TombstoneDto extends ObjectDto
{
    public $type = 'Tombstone';

    public $formerType;

    public $deleted;
}

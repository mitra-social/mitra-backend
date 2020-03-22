<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class RelationshipDto extends ObjectDto
{
    public $type = 'Relationship';

    public $subject;

    public $object;

    public $relationship;
}

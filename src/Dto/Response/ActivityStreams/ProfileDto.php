<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class ProfileDto extends ObjectDto
{
    /**
     * @var string
     */
    public $type = 'Profile';

    /**
     * On a Relationship object, the relationship property identifies the kind of relationship that exists between
     * subject and object.
     * @var null|ObjectDto
     */
    public $describes;
}

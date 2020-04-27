<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class RelationshipDto extends ObjectDto
{
    /**
     * @var string
     */
    public $type = 'Relationship';

    /**
     * On a Relationship object, the subject property identifies one of the connected individuals. For instance, for a
     * Relationship object describing "John is related to Sally", subject would refer to John.
     * @var null|ObjectDto
     */
    public $subject;

    /**
     * When used within a Relationship describes the entity to which the subject is related.
     * @var null|ObjectDto|LinkDto
     */
    public $object;

    /**
     * On a Relationship object, the relationship property identifies the kind of relationship that exists between
     * subject and object.
     * @var null|ObjectDto
     */
    public $relationship;
}

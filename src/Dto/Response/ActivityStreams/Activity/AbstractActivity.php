<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

abstract class AbstractActivity extends ObjectDto
{
    /**
     * Describes one or more entities that either performed or are expected to perform the activity. Any single activity
     * can have multiple actors. The actor MAY be specified using an indirect Link.
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $actor;

    /**
     * Describes the indirect object, or target, of the activity. The precise meaning of the target is largely dependent
     * on the type of action being described but will often be the object of the English preposition "to". For instance,
     * in the activity "John added a movie to his wishlist", the target of the activity is John's wishlist.
     * An activity can have more than one target.
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $target;

    /**
     * Describes the result of the activity. For instance, if a particular action results in the creation of a new
     * resource, the result property can be used to describe that new resource.
     * @var null|ObjectDto|LinkDto
     */
    public $result;

    /**
     * Describes an indirect object of the activity from which the activity is directed. The precise meaning of the
     * origin is the object of the English preposition "from". For instance, in the activity "John moved an item to
     * List B from List A", the origin of the activity is "List A".
     * @var null|ObjectDto|LinkDto
     */
    public $origin;

    /**
     * Identifies one or more objects used (or to be used) in the completion of an Activity.
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $instrument;
}

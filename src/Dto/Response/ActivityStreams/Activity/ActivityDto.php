<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;

class ActivityDto extends AbstractActivity
{
    /**
     * @var string
     */
    public $type = 'Activity';

    /**
     * When used within an Activity, describes the direct object of the activity. For instance, in the activity "John
     * added a movie to his wishlist", the object of the activity is the movie added.
     * @var null|string|ObjectDto|LinkDto|array<LinkDto|ObjectDto|string>
     */
    public $object;
}

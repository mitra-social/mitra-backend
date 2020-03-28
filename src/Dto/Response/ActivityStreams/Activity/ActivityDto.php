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
     * @var null|ObjectDto|LinkDto|array<ObjectDto|LinkDto>
     */
    public $object;
}

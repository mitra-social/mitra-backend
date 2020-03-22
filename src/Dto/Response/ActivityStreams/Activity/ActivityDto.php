<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

class ActivityDto extends AbstractActivity
{
    public $type = 'Activity';

    public $object;
}

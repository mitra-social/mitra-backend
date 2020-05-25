<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams\Activity;

use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;

class IntransitiveActivityDto extends AbstractActivity
{
    /**
     * @var string
     */
    public $type = 'IntransitiveActivity';
}

<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\Entity\ActivityStreamContent;

abstract class AbstractActivityStreamContentCommand
{
    /**
     * @var ActivityStreamContent
     */
    private $activityStreamContent;

    public function __construct(ActivityStreamContent $activityStreamContent)
    {
        $this->activityStreamContent = $activityStreamContent;
    }

    public function getActivityStreamContent(): ActivityStreamContent
    {
        return $this->activityStreamContent;
    }
}

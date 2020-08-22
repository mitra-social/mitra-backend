<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Event\ActivityPub;

use Mitra\MessageBus\EventInterface;
use Mitra\Entity\ActivityStreamContentAssignment;

final class ActivityStreamContentAssignedEvent implements EventInterface
{
    /**
     * @var ActivityStreamContentAssignment
     */
    private $activityStreamContentAssigment;

    public function __construct(ActivityStreamContentAssignment $activityStreamContentAssigment)
    {
        $this->activityStreamContentAssigment = $activityStreamContentAssigment;
    }

    public function getActivityStreamContentAssigment(): ActivityStreamContentAssignment
    {
        return $this->activityStreamContentAssigment;
    }
}

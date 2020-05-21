<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Event\ActivityPub;

use Mitra\CommandBus\EventInterface;
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

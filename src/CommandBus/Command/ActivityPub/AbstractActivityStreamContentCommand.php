<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;

abstract class AbstractActivityStreamContentCommand
{
    /**
     * @var ActivityStreamContent
     */
    private $activityStreamContent;

    /**
     * @var ObjectDto
     */
    private $activityStreamObject;

    public function __construct(ActivityStreamContent $activityStreamContent, ObjectDto $activityStreamObject)
    {
        $this->activityStreamContent = $activityStreamContent;
        $this->activityStreamObject = $activityStreamObject;
    }

    public function getActivityStreamContent(): ActivityStreamContent
    {
        return $this->activityStreamContent;
    }

    /**
     * @return ObjectDto
     */
    public function getActivityStreamObject(): ObjectDto
    {
        return $this->activityStreamObject;
    }
}

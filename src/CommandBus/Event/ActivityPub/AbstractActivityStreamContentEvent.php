<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\CommandBus\EventInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;

abstract class AbstractActivityStreamContentEvent implements EventInterface
{
    /**
     * @var ActivityStreamContent
     */
    private $activityStreamContentEntity;

    /**
     * @var ObjectDto
     */
    private $activityStreamDto;

    public function __construct(ActivityStreamContent $activityStreamContentEntity, ObjectDto $activityStreamDto)
    {
        $this->activityStreamContentEntity = $activityStreamContentEntity;
        $this->activityStreamDto = $activityStreamDto;
    }

    public function getActivityStreamContentEntity(): ActivityStreamContent
    {
        return $this->activityStreamContentEntity;
    }

    public function getActivityStreamDto(): ObjectDto
    {
        return $this->activityStreamDto;
    }
}

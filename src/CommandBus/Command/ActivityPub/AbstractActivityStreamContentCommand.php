<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;

abstract class AbstractActivityStreamContentCommand implements CommandInterface
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

    /**
     * @return ObjectDto
     */
    public function getActivityStreamDto(): ObjectDto
    {
        return $this->activityStreamDto;
    }
}

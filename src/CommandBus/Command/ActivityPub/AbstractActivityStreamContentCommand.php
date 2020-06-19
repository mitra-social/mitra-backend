<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Command\ActivityPub;

use Mitra\CommandBus\CommandInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;

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

    /**
     * @var null|Actor
     */
    private $actor;

    public function __construct(
        ActivityStreamContent $activityStreamContentEntity,
        ObjectDto $activityStreamDto,
        ?Actor $actor
    ) {
        $this->activityStreamContentEntity = $activityStreamContentEntity;
        $this->activityStreamDto = $activityStreamDto;
        $this->actor = $actor;
    }

    public function getActivityStreamContentEntity(): ActivityStreamContent
    {
        return $this->activityStreamContentEntity;
    }

    public function getActivityStreamDto(): ObjectDto
    {
        return $this->activityStreamDto;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }
}

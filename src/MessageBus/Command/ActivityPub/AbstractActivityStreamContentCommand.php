<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Command\ActivityPub;

use Mitra\MessageBus\CommandInterface;
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

    /**
     * @var bool
     */
    private $dereferenceObjects;

    public function __construct(
        ActivityStreamContent $activityStreamContentEntity,
        ObjectDto $activityStreamDto,
        ?Actor $actor,
        bool $dereferenceObjects
    ) {
        $this->activityStreamContentEntity = $activityStreamContentEntity;
        $this->activityStreamDto = $activityStreamDto;
        $this->actor = $actor;
        $this->dereferenceObjects = $dereferenceObjects;
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

    public function shouldDereferenceObjects(): bool
    {
        return $this->dereferenceObjects;
    }
}

<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Event\ActivityPub;

use Mitra\CommandBus\EventInterface;
use Mitra\Dto\Response\ActivityStreams\Activity\UpdateDto;
use Mitra\Entity\Actor\Actor;

final class ExternalUserUpdatedEvent implements EventInterface
{
    /**
     * @var Actor
     */
    private $actorEntity;

    /**
     * @var UpdateDto
     */
    private $updateDto;

    public function __construct(Actor $actorEntity, UpdateDto $updateDto)
    {
        $this->actorEntity = $actorEntity;
        $this->updateDto = $updateDto;
    }

    /**
     * @return Actor
     */
    public function getActorEntity(): Actor
    {
        return $this->actorEntity;
    }

    /**
     * @return UpdateDto
     */
    public function getUpdateDto(): UpdateDto
    {
        return $this->updateDto;
    }
}

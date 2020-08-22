<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Event\ActivityPub;

use Mitra\MessageBus\EventInterface;
use Mitra\Dto\Response\ActivityPub\Actor\ActorInterface;
use Mitra\Entity\Actor\Actor;

final class ExternalUserUpdatedEvent implements EventInterface
{
    /**
     * @var Actor
     */
    private $actorEntity;

    /**
     * @var ActorInterface
     */
    private $actorDto;

    public function __construct(Actor $actorEntity, ActorInterface $actorDto)
    {
        $this->actorEntity = $actorEntity;
        $this->actorDto = $actorDto;
    }

    /**
     * @return Actor
     */
    public function getActorEntity(): Actor
    {
        return $this->actorEntity;
    }

    /**
     * @return ActorInterface
     */
    public function getActorDto(): ActorInterface
    {
        return $this->actorDto;
    }
}
